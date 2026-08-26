<?php
// app/Events/VehicleStatusUpdatedEvent.php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleStatusUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int    $vehicleId,
        public int    $hostUserId,
        public string $status,
        public string $message,
    ) {}

    public function broadcastOn(): array
    {
        return [
            // حدث خاص للهوست
            new PrivateChannel("host.{$this->hostUserId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'vehicle.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'vehicle_id' => $this->vehicleId,
            'status'     => $this->status,
            'message'    => $this->message,
            'timestamp' => now()->format('Y-m-d'),
        ];
    }
}
