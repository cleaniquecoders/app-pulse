<?php

namespace CleaniqueCoders\AppPulse\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use CleaniqueCoders\AppPulse\Notifications\NotificationManager;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendUptimeNotification implements ShouldQueue
{
    public function __construct(protected NotificationManager $notificationManager) {}

    public function handle(MonitorUptimeChanged $event): void
    {
        $this->notificationManager->sendUptimeNotification(
            $event->monitor,
            $event->status->value
        );
    }
}
