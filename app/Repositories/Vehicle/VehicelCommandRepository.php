<?php


namespace App\Repositories\Vehicle;

use App\Jobs\ActivateVehicleSnoozeJob;
use App\Jobs\EndVehicleSnoozeJob;
use App\Models\Host;
use App\Repositories\Vehicle\Interfaces\VehicleCommandRepositoryInterface;
use App\Traits\Upload\UplodeImageHelper;
use App\Models\Notification;
use App\Models\Vehicle;
use App\Models\Image;
use App\Models\VehicleAvailability;
use App\Models\VehicleCustomPricing;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

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
        $uploaded = $this->uploadImage($data['driving_license'], 'resources');

        $host = Host::create([
            'user_id'             => $user->id,
            'is_verified'         => false,
            'delivery_available'  => $data['delivery_available'] ?? false,
            'delivery_fee_per_km' => $data['delivery_fee'] ?? null,
        ]);

        $host->images()->create([
            'path' => $uploaded['path'],
            'hash' => $uploaded['hash'], // ✅ مهم
            'type' => 'driving_license'
        ]);

        return $host;
    }

    private function uploadBooklet(array $data): array
    {
        return $this->uploadImage($data['mechanic_booklet'], 'resources');
    }

    private function createVehicle($host, array $data, array $uploaded): Vehicle
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
            'path' => $uploaded['path'],
            'hash' => $uploaded['hash'],
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

        // آخر ترتيب
        $lastSortOrder = $vehicle->images()->max('sort_order') ?? 0;

        $primaryIndex = $data['primary_image_index'] ?? null;

        foreach ($data['images'] as $index => $imageFile) {

            $uploaded = $this->uploadImage($imageFile, 'vehicles');

            // 🔴 تحقق من التكرار
            $exists = Image::where('hash', $uploaded['hash'])
                ->where('imageable_type', Vehicle::class)
                ->where('imageable_id', $vehicle->id)
                ->exists();

            if ($exists) {
                continue; // تجاهل الصورة المكررة
            }

            $vehicle->images()->create([
                'path'       => $uploaded['path'],
                'hash'       => $uploaded['hash'], // ✅ الجديد
                'type'       => 'vehicle_image',
                'sort_order' => $lastSortOrder + $index + 1,
                'is_primary' => $primaryIndex !== null && $index === (int) $primaryIndex,
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

            $uploaded = $this->uploadBooklet($data);

            $vehicle = $this->createVehicle($host, $data, $uploaded);

            $this->attachFeatures($vehicle, $data);
            $this->attachImages($vehicle, $data);
            $this->attachAvailability($vehicle, $data);

            $this->flushPendingCache();

            return $this->buildStoreResponse($vehicle, $host, $isFirstTime);
        });
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
                'availabilities' => fn($q) => $q->orderBy('available_from')
            ])
            ->where('host_id', $hostId)
            ->findOrFail($vehicleId);
    }

    public function updateBasicInfo(int $vehicleId, int $hostId, array $data): Vehicle
    {
        $vehicle = Vehicle::where('host_id', $hostId)->findOrFail($vehicleId);

        $vehicle->update([
            'make'               => $data['make'] ?? $vehicle->make,
            'model'              => $data['model'] ?? $vehicle->model,
            'year'               => $data['year'] ?? $vehicle->year,
            'color'              => $data['color']            ?? null,
            'fuel_type'          => $data['fuel_type'] ?? $vehicle->fuel_type,
            'transmission'       => $data['transmission']?? $vehicle->transmission,
            'engine_capacity'    => $data['engine_capacity']  ?? null,
            'seats'              => $data['seats'] ?? $vehicle->seats,
            'plate_number'       => $data['plate_number'] ?? $vehicle->plate_number,
            'guest_instructions' => $data['guest_instructions'] ?? null,
        ]);

        return $vehicle->fresh();
    }

    public function updateListingStatus(int $vehicleId, int $hostId, string $status): array
    {
        return DB::transaction(function () use ($vehicleId, $hostId, $status) {

            $vehicle = Vehicle::where('host_id', $hostId)
                ->where('admin_review_status', 'approved')
                ->lockForUpdate()
                ->findOrFail($vehicleId);

            if ($vehicle->listing_status === $status) {
                return ['status' => 'no_change'];
            }

            // ── إذا كان snoozed ونريد listed → نحذف الـ snooze ─
            if ($vehicle->listing_status === 'snoozed' && $status === 'listed') {
                VehicleAvailability::where('vehicle_id', $vehicleId)
                    ->where('type', 'snoozed')
                    ->where('available_to', '>=', now())
                    ->delete();
            }

            $vehicle->update([
                'listing_status' => $status,
            ]);

            return [
                'status'  => 'updated',
                'vehicle' => $vehicle->fresh(),
            ];
        });
    }

    private function checkOverlaping($vehicleId,$snoozeFrom,$snoozeUntil):bool
    {
        return  VehicleAvailability::where('vehicle_id', $vehicleId)
        ->where('is_blocked', true)
        ->where(function ($query) use ($snoozeFrom, $snoozeUntil) {
            $query->where('available_from', '<=', $snoozeUntil)
                ->where('available_to', '>=', $snoozeFrom);
        })
        ->exists();
    }

    public function storeSnooze(int $vehicleId, int $hostId, array $data): array
    {
        return DB::transaction(function () use ($vehicleId, $hostId, $data) {

            $vehicle = Vehicle::where('host_id', $hostId)
                ->where('admin_review_status', 'approved')
                ->lockForUpdate()
                ->findOrFail($vehicleId);

            $snoozeFrom  = Carbon::parse($data['snoozed_from']);
            $snoozeUntil = Carbon::parse($data['snoozed_until']);


            if ($this->checkOverlaping($vehicleId, $snoozeFrom, $snoozeUntil)) {
                return [
                    'status'  => 'error',
                    'message' => 'There is already a snoozed/blocked period overlapping with the selected dates'
                ];
            }
            // ── إضافة سجل snooze في availabilities ───────────
            $snooze = VehicleAvailability::create([
                'vehicle_id'     => $vehicleId,
                'type'           => 'snoozed',
                'available_from' => $snoozeFrom,
                'available_to'   => $snoozeUntil,
                'is_blocked'     => true,
                'block_reason'   => 'snoozed_by_host',
            ]);

            // ── إذا بدأ اليوم → نغير الحالة فوراً ───────────
            if (now()->gte($snoozeFrom)) {
                $vehicle->update([
                    'listing_status' => 'snoozed',
                ]);
            } else {
                // ── مستقبلي → Job يُفعّل الـ snooze لاحقاً ───
                ActivateVehicleSnoozeJob::dispatch(
                    vehicleId  : $vehicleId,
                )->delay($snoozeFrom);
            }

            EndVehicleSnoozeJob::dispatch($snooze->id)
                ->delay($snoozeUntil);

            return [
                'status' => 'snoozed',
                'snooze' => $snooze,
                'vehicle'=> $vehicle->fresh(),
            ];
        });
    }

    public function updatePricing(int $vehicleId, int $hostId, array $data): array
    {
        $vehicle = Vehicle::where('host_id', $hostId)
            ->findOrFail($vehicleId);

        $vehicle->update([
            'base_price_per_day' => $data['base_price_per_day'],
            'delivery_available' => $data['delivery_available'],
            'delivery_fee'       => $data['delivery_available']
                ? $data['delivery_fee']
                : null,
        ]);

        return [
            'status' => 'success'
        ];
    }

    private function hasDateOverlap(int $vehicleId, string $dateFrom, string $dateTo, ?int $ignoreId = null): bool
    {
        return VehicleCustomPricing::where('vehicle_id', $vehicleId)
            ->when($ignoreId, function ($q) use ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            })
            ->where(function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('date_from', [$dateFrom, $dateTo])
                ->orWhereBetween('date_to', [$dateFrom, $dateTo])
                ->orWhere(function ($q) use ($dateFrom, $dateTo) {
                    $q->where('date_from', '<=', $dateFrom)
                        ->where('date_to', '>=', $dateTo);
                });
            })
            ->exists();
    }

    public function storeCustomPricing(int $vehicleId, int $hostId, array $data): array
    {
        // ── نتأكد أن السيارة تخص هذا الهوست ────────────────
        Vehicle::where('host_id', $hostId)->findOrFail($vehicleId);

        // ── نتحقق من تعارض التواريخ ──────────────────────────
        if ($this->hasDateOverlap(
                $vehicleId,
                $data['date_from'],
                $data['date_to']
            )) {
            return [
                'status'  => 'error',
                'message' => 'This period overlaps with an existing custom pricing.'
            ];
        }

        VehicleCustomPricing::create([
            'vehicle_id'    => $vehicleId,
            'date_from'     => $data['date_from'],
            'date_to'       => $data['date_to'],
            'price_per_day' => $data['price_per_day'],
            'reason'        => $data['reason'] ?? null,
        ]);

        return [
            'status' => 'success'
        ];
    }

    public function updateCustomPricing(int $vehicleId, int $hostId, int $pricingId, array $data): array
    {
        // ── نتأكد أن السيارة تخص هذا الهوست ────────────────
        Vehicle::where('host_id', $hostId)->findOrFail($vehicleId);

        $pricing = VehicleCustomPricing::where('vehicle_id', $vehicleId)
            ->findOrFail($pricingId);

        // ── نتحقق من تعارض التواريخ مع استثناء السجل الحالي ─
        if ($this->hasDateOverlap(
            $vehicleId,
            $data['date_from'],
            $data['date_to'],
            $pricingId // تجاهل السجل الحالي
        )) {
            return [
                'status'  => 'error',
                'message' => 'This period overlaps with an existing custom pricing.'
            ];
        }

        $pricing->update([
            'date_from'     => $data['date_from'],
            'date_to'       => $data['date_to'],
            'price_per_day' => $data['price_per_day'],
            'reason'        => $data['reason'] ?? null,
        ]);

        return [
            'data' =>[],
            'message' => '',
            'status' => 'success'
        ];
    }

    public function destroyCustomPricing(int $vehicleId, int $hostId, int $pricingId): bool
    {
        Vehicle::where('host_id', $hostId)->findOrFail($vehicleId);

        $pricing = VehicleCustomPricing::where('vehicle_id', $vehicleId)
            ->findOrFail($pricingId);

        return $pricing->delete();
    }

    public function uploadImages(int $vehicleId, int $hostId, array $data): bool
    {
        return DB::transaction(function () use ($vehicleId, $hostId, $data) {

            $vehicle = Vehicle::where('host_id', $hostId)->findOrFail($vehicleId);

            $primaryIndex = $data['primary_image_index'] ?? null;

            // إذا المستخدم حدد صورة أساسية → نلغي القديمة
            if ($primaryIndex !== null) {
                $vehicle->images()
                    ->where('type', 'vehicle_image')
                    ->update(['is_primary' => false]);
            }

            // رفع الصور (مع منع التكرار)
            $this->attachImages($vehicle, $data);

            $hasPrimary = $vehicle->images()
                ->where('type', 'vehicle_image')
                ->where('is_primary', true)
                ->exists();

            if (!$hasPrimary) {
                $firstImage = $vehicle->images()
                    ->where('type', 'vehicle_image')
                    ->orderBy('sort_order')
                    ->first();

                if ($firstImage) {
                    $firstImage->update(['is_primary' => true]);
                }
            }


            return true;
        });
    }

    public function destroyImage(int $vehicleId, int $hostId, int $imageId): bool
    {
        Vehicle::where('host_id', $hostId)->findOrFail($vehicleId);

        $image = Image::where('imageable_type', Vehicle::class)
            ->where('imageable_id', $vehicleId)
            ->where('type', 'vehicle_image')
            ->findOrFail($imageId);

        $wasPrimary = $image->is_primary;

        // ── حذف الملف من Storage ─────────────────────────────

        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        // ── إذا كانت primary → نجعل أول صورة متبقية primary ──
        if ($wasPrimary) {
            $firstImage = Image::where('imageable_type', Vehicle::class)
                ->where('imageable_id', $vehicleId)
                ->where('type', 'vehicle_image')
                ->orderBy('sort_order')
                ->first();

            $firstImage?->update(['is_primary' => true]);
        }

        return true;
    }

    public function setPrimaryImage(int $vehicleId, int $hostId, int $imageId): bool
    {
        return DB::transaction(function () use ($vehicleId, $hostId, $imageId) {

            Vehicle::where('host_id', $hostId)->findOrFail($vehicleId);

            // ── نتحقق أن الصورة تخص هذه السيارة ────────────
            $image = Image::where('imageable_type', Vehicle::class)
                ->where('imageable_id', $vehicleId)
                ->where('type', 'vehicle_image')
                ->findOrFail($imageId);

            // ── نلغي الـ primary من كل الصور ─────────────────
            Image::where('imageable_type', Vehicle::class)
                ->where('imageable_id', $vehicleId)
                ->where('type', 'vehicle_image')
                ->update(['is_primary' => false]);

            // ── نضع الصورة الجديدة كـ primary ────────────────
            $image->update(['is_primary' => true]);

            return true;
        });
    }

    public function syncFeatures(int $vehicleId, int $hostId, array $featureIds): array
    {
        $vehicle = Vehicle::where('host_id', $hostId)->findOrFail($vehicleId);

        // sync يضيف الجديد ويحذف المحذوف دفعة واحدة
        $vehicle->features()->sync($featureIds);

        return [
            'status' => 'success'
        ];
    }

    public function updateAvailability(int $vehicleId, int $hostId, array $data): array
    {
        return DB::transaction(function () use ($vehicleId, $hostId, $data) {

            $vehicle = Vehicle::where('host_id', $hostId)
                ->lockForUpdate()
                ->findOrFail($vehicleId);

            $availability = VehicleAvailability::where('vehicle_id', $vehicleId)
                ->where('type', 'available')
                ->first();

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // السيناريو الاول: محجوب من النظام
            // → نعيد تفعيل الإتاحة + نعيد السيارة للقائمة
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            if ($availability->is_blocked && $availability->blocked_by === 'system') {
                $availability->update([
                    'available_from' => $data['available_from'],
                    'available_to'   => $data['available_to'],
                    'is_blocked'     => false,
                    'blocked_by'     => null,
                    'block_reason'   => null,
                ]);

                // نعيد السيارة للقائمة تلقائياً
                $vehicle->update([
                    'listing_status' => 'listed',
                ]);

                return [
                    'status'       => 'reactivated',
                    'availability' => $availability->fresh(),
                    'vehicle'      => $vehicle->fresh(),
                ];
            }

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // السيناريو الثاني: إتاحة عادية نشطة
            // → تعديل التواريخ فقط
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            $availability->update([
                'available_from' => $data['available_from'],
                'available_to'   => $data['available_to'],
            ]);

            return [
                'status'       => 'updated',
                'availability' => $availability->fresh(),
                'vehicle'      => $vehicle->fresh(),
            ];
        });
    }

    public function updateLocation(int $vehicleId, int $hostId, array $data): array
    {
        $vehicle = Vehicle::where('host_id', $hostId)->findOrFail($vehicleId);

        $vehicle->update([
            'city'           => $data['city'],
            'pickup_address' => $data['pickup_address'],
            'pickup_lat'     => $data['pickup_lat'],
            'pickup_lng'     => $data['pickup_lng'],
        ]);

        return [
            'staus' => 'success'
        ];
    }
}
