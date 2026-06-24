<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AvailabilityReminderEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $data;

    public function __construct($userId, $vehicleId)
    {
        $this->userId = $userId;

        $this->data = [
            'type' => 'availability_reminder',
            'title' => 'Availability Ending Soon',
            'body' => 'Your vehicle will expire within 24 hours.',
            'vehicle_id' => $vehicleId,
        ];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('host.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'availability.reminder';
    }

    public function broadcastWith()
    {
        return $this->data;
    }
}
