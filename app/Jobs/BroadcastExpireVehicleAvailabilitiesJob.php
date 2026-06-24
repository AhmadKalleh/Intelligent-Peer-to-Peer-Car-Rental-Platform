<?php

namespace App\Jobs;

use App\Events\AvailabilityExpiredEvent;
use App\Models\Notification;
use App\Models\Vehicle;
use App\Models\VehicleAvailability;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastExpireVehicleAvailabilitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        VehicleAvailability::query()
            ->with(['vehicle.host.user'])
            ->where('available_to', '<', now())
            ->where('is_blocked', false)
            ->chunkById(100, function ($availabilities) {

                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                // 1. Bulk block لكل السجلات المنتهية
                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                $expiredIds = $availabilities->pluck('id')->toArray();

                VehicleAvailability::whereIn('id', $expiredIds)->update([
                    'is_blocked'   => true,
                    'block_reason' => 'expired_by_system',
                    'updated_at'   => now(),
                ]);

                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                // 2. السيارات الفريدة في هذا الـ chunk
                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                $vehicles = $availabilities
                    ->pluck('vehicle')
                    ->unique('id')
                    ->filter();

                $vehicleIds = $vehicles->pluck('id')->toArray();

                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                // 3. query واحدة للسيارات التي لها إتاحة نشطة
                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                $vehiclesWithActiveAvailability = VehicleAvailability::query()
                    ->whereIn('vehicle_id', $vehicleIds)
                    ->where('available_to', '>=', now())
                    ->where('is_blocked', false)
                    ->pluck('vehicle_id')
                    ->toArray();

                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                // 4. السيارات التي ليس لها إتاحة نشطة → unlist
                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                $vehiclesToUnlist = $vehicles->filter(
                    fn($v) => !in_array($v->id, $vehiclesWithActiveAvailability)
                );

                if ($vehiclesToUnlist->isNotEmpty()) {
                    Vehicle::whereIn('id', $vehiclesToUnlist->pluck('id')->toArray())
                        ->where('listing_status', 'listed')
                        ->update([
                            'listing_status' => 'unlisted',
                            'updated_at'     => now(),
                        ]);
                }

                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                // 5. Notifications bulk + Broadcast لكل هوست
                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                $notifications = [];

                foreach ($vehiclesToUnlist as $vehicle) {
                    $hostUser = $vehicle->host?->user;

                    if (!$hostUser) continue;

                    $notifications[] = [
                        'user_id'    => $hostUser->id,
                        'type'       => 'availability_expired',
                        'title'      => 'Vehicle Unlisted',
                        'body'       => "Your vehicle {$vehicle->make} {$vehicle->model} availability has expired and has been unlisted.",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // ── Broadcast لكل هوست ────────────────────
                    broadcast(new AvailabilityExpiredEvent(
                        userId: $hostUser->id,
                        vehicleId : $vehicle->id,
                    ))->toOthers();
                }

                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                // 6. Bulk insert notifications
                // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                if (!empty($notifications)) {
                    Notification::insert($notifications);
                }
            });
    }
}
