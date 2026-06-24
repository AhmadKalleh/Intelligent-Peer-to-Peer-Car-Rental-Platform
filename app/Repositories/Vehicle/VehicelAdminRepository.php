<?php


namespace App\Repositories\Vehicle;

use App\Models\Notification;
use App\Models\Vehicle;
use App\Repositories\Vehicle\Interfaces\VehicleAdminRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class VehicelAdminRepository implements VehicleAdminRepositoryInterface
{
    private string $pendingCacheKey = 'admin:vehicles:pending:page:';

    private function approveVehicle(Vehicle $vehicle, int $adminId): void
    {
        $vehicle->update([
            'admin_review_status' => 'approved',
            'listing_status'      => 'listed',
            'reviewed_by_user_id' => $adminId,
            'reviewed_at'         => now(),
        ]);
    }

    private function verifyHost($host, $hostUser): void
    {
        $host->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        $this->assignHostRole($hostUser);
    }

    private function assignHostRole($user): void
    {
        if ($user->hasRole('host')) {
            return;
        }

        $hostRole = Role::query()->where('name', '=', 'host')->first();

        if (!$hostRole) {
            throw new \Exception('Host role not found');
        }

        $user->assignRole($hostRole);
        $user->givePermissionTo($hostRole->permissions->pluck('name')->toArray());
    }

    private function sendApprovalNotification(int $userId, Vehicle $vehicle): void
    {
        Notification::create([
            'user_id' => $userId,
            'type'    => 'vehicle_approved',
            'title'   => 'Vehicle Approved!',
            'body'    => "Your vehicle {$vehicle->make} {$vehicle->model} has been approved.",
        ]);
    }

    private function buildApproveResponse(Vehicle $vehicle, int $userId, bool $isFirstTime): array
    {
        return [
            'status'        => 'approved',
            'vehicle'       => $vehicle->fresh(['host.user']),
            'host_user_id'  => $userId,
            'is_first_time' => $isFirstTime,
        ];
    }

    public function approve(int $vehicleId, int $adminId): array
    {
        return DB::transaction(function () use ($vehicleId, $adminId) {

            $vehicle = $this->getVehicleForUpdate($vehicleId);

            if ($vehicle->admin_review_status !== 'pending') {
                return ['status' => 'not_pending'];
            }

            $this->approveVehicle($vehicle, $adminId);

            $host        = $vehicle->host;
            $hostUser    = $host->user;
            $isFirstTime = !$host->is_verified;

            if ($isFirstTime) {
                $this->verifyHost($host, $hostUser);
            }

            $this->sendApprovalNotification($hostUser->id, $vehicle);
            $this->flushVehicleCache();
            return $this->buildApproveResponse($vehicle, $hostUser->id, $isFirstTime);
        });
    }

    private function getVehicleForUpdate(int $vehicleId): Vehicle
    {
        return Vehicle::with(['host.user', 'images', 'host.images'])
            ->lockForUpdate()
            ->findOrFail($vehicleId);
    }

    private function handleFirstTimeRejection(Vehicle $vehicle, $host): void
    {
        $this->deleteVehicleFiles($vehicle);
        $this->deleteHostFiles($host);

        $vehicle->delete();
        $host->delete();
    }

    private function handleExistingHostRejection(Vehicle $vehicle, int $adminId, string $reason,bool $isFirstTime=false): void
    {
        $this->deleteVehicleFiles($vehicle,$isFirstTime);

        $vehicle->update([
            'admin_review_status'    => 'rejected',
            'admin_rejection_reason' => $reason,
            'reviewed_by_user_id'    => $adminId,
            'reviewed_at'            => now(),
        ]);
    }

    private function deleteVehicleFiles(Vehicle $vehicle, bool $isFirstTime = false): void
    {
        // ── صور السيارة + دفتر الميكانيك ─────────────
        $vehiclePaths = $vehicle->images()
            ->whereIn('type', ['vehicle_image', 'mechanic_booklet'])
            ->pluck('path');

        // ── شهادة القيادة فقط لأول مرة ───────────────
        $licensePaths = collect();

        if ($isFirstTime && $vehicle->host) {
            $licensePaths = $vehicle->host->images()
                ->where('type', 'driving_license')
                ->pluck('path');
        }

        // ── دمج ─────────────────────────────────────
        $paths = $vehiclePaths
            ->merge($licensePaths)
            ->filter()
            ->unique()
            ->toArray();

        // ── حذف من storage ─────────────────────────
        if (!empty($paths)) {
            Storage::disk('public')->delete($paths);
        }

        // ── حذف من DB ──────────────────────────────
        $vehicle->images()
            ->whereIn('type', ['vehicle_image', 'mechanic_booklet'])
            ->delete();

        // شهادة القيادة فقط أول مرة
        if ($isFirstTime && $vehicle->host) {
            $vehicle->host->images()
                ->where('type', 'driving_license')
                ->delete();
        }
    }

    private function deleteHostFiles($host): void
    {
        foreach ($host->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
    }

    private function notifyRejection(int $userId, Vehicle $vehicle, string $reason): void
    {
        Notification::create([
            'user_id' => $userId,
            'type'    => 'vehicle_rejected',
            'title'   => 'Vehicle Rejected',
            'body'    => "Your vehicle {$vehicle->make} {$vehicle->model} was rejected. Reason: {$reason}",
        ]);
    }

    public function reject(int $vehicleId, int $adminId, string $reason): array
    {
        return DB::transaction(function () use ($vehicleId, $adminId, $reason) {

            $vehicle = $this->getVehicleForUpdate($vehicleId);

            if ($vehicle->admin_review_status !== 'pending') {
                return ['status' => 'not_pending'];
            }

            $host        = $vehicle->host;
            $hostUser    = $host->user;
            $isFirstTime = !$host->is_verified;

            if ($isFirstTime) {
                $this->handleFirstTimeRejection($vehicle, $host);
            } else {
                $this->handleExistingHostRejection($vehicle, $adminId, $reason,$isFirstTime);
            }

            $this->notifyRejection($hostUser->id, $vehicle, $reason);

            return $this->buildResponse($vehicle, $hostUser->id, $isFirstTime);
        });
    }

    private function buildResponse(Vehicle $vehicle, int $userId, bool $isFirstTime): array
    {
        return [
            'status'        => 'rejected',
            'is_first_time' => $isFirstTime,
            'host_user_id'  => $userId,
            'vehicle_make'  => $vehicle->make,
            'vehicle_model' => $vehicle->model,
        ];
    }

    private function flushVehicleCache(): void
    {
        Redis::del([
            'home_section:cities',
            'home_section:delivery',
            'home_section:airports',
        ]);
    }


    public function getPendingVehicles(int $perPage = 10): LengthAwarePaginator
    {
        $page     = request()->get('page', 1);
        $cacheKey = $this->pendingCacheKey . $page . ':' . $perPage;
        $cached   = Redis::get($cacheKey);

        if ($cached) {
            return unserialize($cached);
        }

        $paginated = Vehicle::query()
            ->with(['primaryImage', 'host.user.image'])
            ->where('admin_review_status', 'pending')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        // كاش 5 دقائق فقط لأن الطلبات تتغير بسرعة
        Redis::setex($cacheKey, 300, serialize($paginated));

        return $paginated;
    }

    public function showPending(int $vehicleId): Vehicle
    {
        return Vehicle::query()
            ->with([
                // صور السيارة فقط
                'images' => fn($q) => $q
                    ->where('type', 'vehicle_image')
                    ->orderBy('sort_order'),

                // دفتر الميكانيك مرتبط بالسيارة
                'mechanicBooklet' => fn($q) => $q
                    ->where('type', 'mechanic_booklet'),

                'features',

                // الهوست مع شهادة القيادة فقط
                'host.user.image',
                'host.drivingLicense',  // صورة الشهادة من هوست

            ])
            ->where('admin_review_status', 'pending')
            ->findOrFail($vehicleId);
    }
}
