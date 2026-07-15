<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Models\VehicleAvailability;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class EndVehicleSnoozeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $snoozeId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {

                $snooze = VehicleAvailability::lockForUpdate()
                    ->find($this->snoozeId);

                // ❗ إذا محذوف مسبقاً → لا تفعل شيء
                if (!$snooze) {
                    return;
                }

                // ❗ حماية إضافية (لو اشتغل بدري أو متأخر)
                if ($snooze->available_to > now()) {
                    return;
                }

                $vehicle = Vehicle::lockForUpdate()
                    ->find($snooze->vehicle_id);

                // 🧨 احذف snooze
                $snooze->delete();

                // 🔥 رجّع السيارة للحالة الطبيعية
                if ($vehicle) {
                    $vehicle->update([
                        'listing_status' => 'listed',
                    ]);
                }

        });
    }
}
