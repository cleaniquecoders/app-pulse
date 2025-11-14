<?php

namespace CleaniqueCoders\AppPulse\Listeners;

use CleaniqueCoders\AppPulse\Events\SslStatusChanged;
use CleaniqueCoders\AppPulse\Notifications\NotificationManager;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSslNotification implements ShouldQueue
{
    public function __construct(protected NotificationManager $notificationManager) {}

    public function handle(SslStatusChanged $event): void
    {
        $this->notificationManager->sendSslNotification(
            $event->monitor,
            $event->status->value
        );
    }
}
