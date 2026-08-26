<?php

namespace App\Jobs;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ActivateVehicleSnoozeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $vehicleId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $vehicle = Vehicle::find($this->vehicleId);

        if ($vehicle) {
            $vehicle->update([
                'listing_status' => 'snoozed'
            ]);
        }
    }
}
