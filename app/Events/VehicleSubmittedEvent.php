<?php
// app/Events/VehicleSubmittedEvent.php

namespace App\Events;

use App\Models\Vehicle;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleSubmittedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Vehicle $vehicle,
        public bool    $isFirstTime,
    ) {}

    // يرسل للأدمن فقط
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'vehicle.submitted';
    }

    public function broadcastWith(): array
    {
        return [
            'vehicle_id'    => $this->vehicle->id,
            'make'          => $this->vehicle->make,
            'model'         => $this->vehicle->model,
            'is_first_time' => $this->isFirstTime,
            'host_id'       => $this->vehicle->host_id,
            'message'       => $this->isFirstTime
                ? 'New host registration with vehicle submission requires review.'
                : 'New vehicle submission requires review.',
            'timestamp' => now()->format('Y-m-d'),
        ];
    }
}
