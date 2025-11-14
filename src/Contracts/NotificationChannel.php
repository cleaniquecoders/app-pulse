<?php

namespace CleaniqueCoders\AppPulse\Contracts;

use CleaniqueCoders\AppPulse\Models\Monitor;

interface NotificationChannel
{
    /**
     * Send a notification for monitor uptime change
     */
    public function sendUptimeNotification(Monitor $monitor, string $status): void;

    /**
     * Send a notification for SSL status change
     */
    public function sendSslNotification(Monitor $monitor, string $status): void;

    /**
     * Send a notification for performance degradation
     */
    public function sendPerformanceDegradedNotification(Monitor $monitor, float $responseTime, float $threshold): void;

    /**
     * Check if the channel is enabled
     */
    public function isEnabled(): bool;
}
