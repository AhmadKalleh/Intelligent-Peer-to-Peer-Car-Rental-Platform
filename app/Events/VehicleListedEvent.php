<?php

// app/Events/VehicleListedEvent.php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleListedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $vehicleId,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('vehicles.listing'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'vehicle.listed';
    }

    public function broadcastWith(): array
    {
        return [
            'vehicle_id' => $this->vehicleId,
            'message'    => 'A new vehicle is now available.',
            'timestamp'  => now()->format('Y-m-d'),
        ];
    }
}
