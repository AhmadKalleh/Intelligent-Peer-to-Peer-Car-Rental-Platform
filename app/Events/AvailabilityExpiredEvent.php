<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AvailabilityExpiredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $data;

    public function __construct($userId, $vehicleId)
    {
        $this->userId = $userId;

        $this->data = [
            'type' => 'availability_expired',
            'title' => 'Vehicle Unlisted',
            'body' => 'Your vehicle availability has expired and has been unlisted.',
            'vehicle_id' => $vehicleId,
        ];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('host.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'availability.expired';
    }

    public function broadcastWith()
    {
        return $this->data;
    }
}
