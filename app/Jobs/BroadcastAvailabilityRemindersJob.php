<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\VehicleAvailability;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Notification\NotificationService;

class BroadcastAvailabilityRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        VehicleAvailability::whereDate('available_to', '=', now()->addDay()->toDateString())
            ->where('type', 'available')
            ->where('is_blocked', false)
            ->chunkById(100, function ($items) {

                $notificationService = app(NotificationService::class);

                foreach ($items as $availability) {

                    $vehicle = $availability->vehicle;
                    $user    = $vehicle->host->user;

                    $vehicleName = "{$vehicle->brand} {$vehicle->model} {$vehicle->year}";
                    $expiryDate  = $availability->available_to->format('Y-m-d');

                    $notificationService->send(
                        userId         : $user->id,
                        type           : 'availability_reminder',
                        title          : 'Availability Ending Soon',
                        body           : "Your {$vehicleName} will no longer be available after {$expiryDate}. Please update availability to keep it listed.",
                    );

                }
            });
    }
}
