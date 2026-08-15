<?php
// app/Jobs/BroadcastBookingLocationUpdatedJob.php

namespace App\Jobs;

use App\Events\BookingLocationUpdatedEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastBookingLocationUpdatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int    $bookingId,
        public string $role,
        public float  $lat,
        public float  $lng,
        public ?float $hostLat,
        public ?float $hostLng,
        public ?float $guestLat,
        public ?float $guestLng,
        public ?float $distanceKm,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        broadcast(new BookingLocationUpdatedEvent(
            bookingId : $this->bookingId,
            role      : $this->role,
            lat       : $this->lat,
            lng       : $this->lng,
            hostLat   : $this->hostLat,
            hostLng   : $this->hostLng,
            guestLat  : $this->guestLat,
            guestLng  : $this->guestLng,
            distanceKm: $this->distanceKm,
        ))->toOthers();
    }
}
