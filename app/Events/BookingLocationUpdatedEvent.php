<?php
// app/Events/BookingLocationUpdatedEvent.php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingLocationUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int     $bookingId,
        public string  $role,          // 'host' أو 'guest' - مين حدّث موقعو
        public float   $lat,
        public float   $lng,
        public ?float  $hostLat,
        public ?float  $hostLng,
        public ?float  $guestLat,
        public ?float  $guestLng,
        public ?float  $distanceKm,
    ) {}

    public function broadcastOn(): array
    {
        return [
            // قناة خاصة بالحجز - فقط طرفي الحجز (المالك والمستأجر) يسمعوها
            new PrivateChannel("booking-tracking.{$this->bookingId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'booking.location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'booking_id'  => $this->bookingId,
            'updated_by'  => $this->role, // مين بعث آخر تحديث
            'host'        => [
                'lat' => $this->hostLat,
                'lng' => $this->hostLng,
            ],
            'guest'       => [
                'lat' => $this->guestLat,
                'lng' => $this->guestLng,
            ],
            'distance_km' => $this->distanceKm,
            'timestamp'   => now()->toDateTimeString(),
        ];
    }
}
