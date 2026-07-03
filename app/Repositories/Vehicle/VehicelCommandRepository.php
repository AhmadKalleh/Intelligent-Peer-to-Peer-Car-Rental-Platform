<?php


namespace App\Repositories\Vehicle;

use App\Models\Host;
use App\Repositories\Vehicle\Interfaces\VehicleCommandRepositoryInterface;
use App\Traits\Upload\UplodeImageHelper;
use App\Models\Notification;
use App\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class VehicelCommandRepository implements VehicleCommandRepositoryInterface
{
    use UplodeImageHelper;

    private function resolveHost($user, array $data, bool $isFirstTime)
    {
        if ($isFirstTime) {

            if ($user->host !== null) {
                return ['status' => 'already_host'];
            }

            return $this->createHost($user, $data);
        }

        if (!$user->host) {
            return ['status' => 'not_host'];
        }

        return $user->host;
    }

    private function createHost($user, array $data)
    {
        $licensePath = $this->uplodeImage($data['driving_license'], 'resources');

        $host = Host::create([
            'user_id'             => $user->id,
            'is_verified'         => false,
            'delivery_available'  => $data['delivery_available'] ?? false,
            'delivery_fee_per_km' => $data['delivery_fee'] ?? null,
        ]);

        $host->images()->create([
            'path' => $licensePath,
            'type' => 'driving_license'
        ]);

        return $host;
    }

    private function uploadBooklet(array $data): string
    {
        return $this->uplodeImage($data['mechanic_booklet'], 'resources');
    }

    private function createVehicle($host, array $data, string $bookletPath): Vehicle
    {
        $vehicle = Vehicle::create([
            'host_id'              => $host->id,
            'make'                 => $data['make'],
            'model'                => $data['model'],
            'year'                 => $data['year'],
            'fuel_type'            => $data['fuel_type'],
            'transmission'         => $data['transmission'],
            'seats'                => $data['seats'],
            'plate_number'         => $data['plate_number'],
            'mechanic_booklet_url' => $bookletPath,
            'base_price_per_day'   => $data['base_price_per_day'],
            'listing_status'       => 'unlisted',
            'admin_review_status'  => 'pending',

            // optional
            'color'               => $data['color'] ?? null,
            'engine_capacity'     => $data['engine_capacity'] ?? null,
            'delivery_available'  => $data['delivery_available'] ?? false,
            'delivery_fee'        => $data['delivery_fee'] ?? null,
            'pickup_address'      => $data['pickup_address'] ?? null,
            'pickup_lat'          => $data['pickup_lat'] ?? null,
            'pickup_lng'          => $data['pickup_lng'] ?? null,
            'city'                => $data['city'] ?? null,
            'guest_instructions'  => $data['guest_instructions'] ?? null,
        ]);

        // 🔥 مهم: ربط booklet كصورة (لو فعلاً بدك)
        $vehicle->images()->create([
            'path' => $bookletPath,
            'type' =>'mechanic_booklet'
        ]);

        return $vehicle;
    }

    private function attachFeatures(Vehicle $vehicle, array $data): void
    {
        if (!empty($data['features'])) {
            $vehicle->features()->sync($data['features']);
        }
    }

    private function attachImages(Vehicle $vehicle, array $data): void
    {
        if (empty($data['images'])) return;

        $primaryIndex = $data['primary_image_index'] ?? 0;

        foreach ($data['images'] as $index => $imageFile) {
            $path = $this->uplodeImage($imageFile, 'vehicles');

            $vehicle->images()->create([
                'path'       => $path,
                'sort_order' => $index,
                'is_primary' => $index == $primaryIndex,
                'type' =>'vehicle_image'
            ]);
        }
    }

    private function attachAvailability(Vehicle $vehicle, array $data): void
    {
        if (empty($data['available_from']) || empty($data['available_to'])) {
            return;
        }

        $vehicle->availabilities()->create([
            'available_from' => $data['available_from'],
            'available_to'   => $data['available_to'],
            'is_blocked'     => false,
        ]);
    }

    private function flushPendingCache(): void
    {
        $prefix = config('database.redis.options.prefix', '');
        $cursor = 0;

        do {
            [$cursor, $keys] = Redis::scan($cursor, [
                'match' => $prefix . 'admin:vehicles:pending:*',
                'count' => 100,
            ]);

            if (!empty($keys)) {
                // نحذف الـ prefix قبل الحذف لأن Laravel يضيفه تلقائياً
                $keys = array_map(fn($key) => str_replace($prefix, '', $key), $keys);
                Redis::del($keys);
            }

        } while ($cursor != 0);
    }

    public function store(array $data, bool $isFirstTime): array
    {
        return DB::transaction(function () use ($data, $isFirstTime) {

            $user = auth()->user();

            $host = $this->resolveHost($user, $data, $isFirstTime);

            if (is_array($host)) {
                return $host;
            }

            $bookletPath = $this->uploadBooklet($data);

            $vehicle = $this->createVehicle($host, $data, $bookletPath);

            $this->attachFeatures($vehicle, $data);
            $this->attachImages($vehicle, $data);
            $this->attachAvailability($vehicle, $data);

            $this->notifyAdmins($vehicle, $isFirstTime);
            $this->flushPendingCache();

            return $this->buildStoreResponse($vehicle, $host, $isFirstTime);
        });
    }

    private function notifyAdmins(Vehicle $vehicle, bool $isFirstTime): void
    {
        $admins = \App\Models\User::role('admin')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type'    => 'vehicle_pending_review',
                'title'   => $isFirstTime ? 'New Host Registration' : 'New Vehicle Submission',
                'body'    => $isFirstTime
                    ? "New host registration with vehicle {$vehicle->make} {$vehicle->model} requires your review."
                    : "Vehicle {$vehicle->make} {$vehicle->model} submitted for review.",
            ]);
        }
    }

    private function buildStoreResponse(Vehicle $vehicle, $host, bool $isFirstTime): array
    {
        return [
            'status'        => 'pending',
            'vehicle'       => $vehicle->load(['features', 'images']),
            'host'          => $host,
            'is_first_time' => $isFirstTime,
        ];
    }

    public function getHostVehicles(int $hostId, string $status = 'all', int $perPage = 10): LengthAwarePaginator
    {
        $query = Vehicle::query()
            ->select([
                'vehicles.id',
                'vehicles.make',
                'vehicles.model',
                'vehicles.year',
                'vehicles.city',
                'vehicles.base_price_per_day',
                'vehicles.listing_status',
                'vehicles.admin_review_status',
                'vehicles.total_bookings',
                'vehicles.total_reviews',
                'vehicles.rating_avg',
                'vehicles.delivery_available',
                'vehicles.created_at',
            ])
            ->with(['primaryImage'])
            ->withCurrentPrice()
            ->where('host_id', $hostId);

        match ($status) {
            // السيارات المعتمدة بكل حالاتها (listed, unlisted, snoozed)
            'approved' => $query->where('admin_review_status', 'approved'),

            // السيارات المرفوضة
            'rejected' => $query->where('admin_review_status', 'rejected'),

            // السيارات المعلقة
            'pending'  => $query->where('admin_review_status', 'pending'),

            // كل السيارات بدون فلتر
            default    => null,
        };

        return $query
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function showForHost(int $vehicleId, int $hostId): Vehicle
    {
        return Vehicle::query()
            ->select(['vehicles.*'])
            ->withCurrentPrice()
            ->withCustomPriceStatus()
            ->with([
                'customPricings' => fn($q) => $q->orderBy('date_from'),
                'images',
                'mechanicBooklet',
                'features',
                'availabilities' => fn($q) => $q->orderBy('available_from'),
            ])
            ->where('host_id', $hostId)
            ->findOrFail($vehicleId);
    }
}
