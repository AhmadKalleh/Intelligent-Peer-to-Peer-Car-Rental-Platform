<?php

namespace App\Jobs;

use App\Events\VehicleListedEvent;
use App\Events\VehicleStatusUpdatedEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastVehicleStatusUpdatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int    $vehicleId,
        public int    $hostUserId,
        public string $status,
        public string $message,
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        broadcast(new VehicleStatusUpdatedEvent(
            vehicleId: $this->vehicleId,
            hostUserId: $this->hostUserId,
            status: $this->status,
            message: $this->message
        ))->toOthers();

        if ($this->status === 'approved') {
            broadcast(new VehicleListedEvent(
                vehicleId: $this->vehicleId,
        ))->toOthers();
    }
    }
}
