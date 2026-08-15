<?php
// app/Jobs/Booking/BroadcastNewBookingConfirmedJob.php

namespace App\Jobs;

use App\Events\NewBookingConfirmedEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastNewBookingConfirmedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int    $hostUserId,
        public int    $bookingId,
        public string $vehicleName,
        public string $startDate,
        public string $endDate,
        public float  $totalAmount,
    ) {}

    public function handle(): void
    {
        broadcast(new NewBookingConfirmedEvent(
            hostUserId  : $this->hostUserId,
            bookingId   : $this->bookingId,
            vehicleName : $this->vehicleName,
            startDate   : $this->startDate,
            endDate     : $this->endDate,
            totalAmount : $this->totalAmount,
        ));
    }
}
