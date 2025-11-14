<?php

namespace CleaniqueCoders\AppPulse\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorResponseTimeDegraded;
use CleaniqueCoders\AppPulse\Notifications\NotificationManager;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPerformanceDegradedNotification implements ShouldQueue
{
    public function __construct(protected NotificationManager $notificationManager) {}

    public function handle(MonitorResponseTimeDegraded $event): void
    {
        $this->notificationManager->sendPerformanceDegradedNotification(
            $event->monitor,
            $event->responseTime,
            $event->threshold
        );
    }
}
