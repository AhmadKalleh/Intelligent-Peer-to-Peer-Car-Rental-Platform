<?php
// app/Jobs/BroadcastVehicleSubmittedJob.php

namespace App\Jobs;

use App\Events\VehicleSubmittedEvent;
use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastVehicleSubmittedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Vehicle $vehicle,
        public bool    $isFirstTime,
    ) {}

    public function handle(): void
    {
        broadcast(new VehicleSubmittedEvent(
            vehicle     : $this->vehicle,
            isFirstTime : $this->isFirstTime,
        ))->toOthers();
    }
}
