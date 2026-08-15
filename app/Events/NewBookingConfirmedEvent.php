<?php
// app/Events/Booking/NewBookingConfirmedEvent.php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewBookingConfirmedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $hostUserId,
        public int $bookingId,
        public string $vehicleName,
        public string $startDate,
        public string $endDate,
        public float $totalAmount,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("host.{$this->hostUserId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'booking.confirmed';
    }

    public function broadcastWith(): array
    {
        return [
            'booking_id'   => $this->bookingId,
            'vehicle_name' => $this->vehicleName,
            'start_date'   => $this->startDate,
            'end_date'     => $this->endDate,
            'total_amount' => $this->totalAmount,
            'message'      => 'You have a new confirmed booking!',
            'timestamp'    => now()->toISOString(),
        ];
    }
}
