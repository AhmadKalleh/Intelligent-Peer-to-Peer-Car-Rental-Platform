<?php
// app/Jobs/Notification/BroadcastNotificationJob.php

namespace App\Jobs;

use App\Events\NotificationSentEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int     $userId,
        public string  $type,
        public string  $title,
        public ?string $body,
    ) {}

    public function handle(): void
    {
        broadcast(new NotificationSentEvent(
            userId : $this->userId,
            type   : $this->type,
            title  : $this->title,
            body   : $this->body,
        ));
    }
}
