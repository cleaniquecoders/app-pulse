<?php

namespace CleaniqueCoders\AppPulse\Notifications\Channels;

use CleaniqueCoders\AppPulse\Contracts\NotificationChannel;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Support\Facades\Http;

abstract class AbstractNotificationChannel implements NotificationChannel
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get webhook URL from config
     */
    abstract protected function getWebhookUrl(): ?string;

    /**
     * Format the payload for this channel
     */
    abstract protected function formatPayload(string $title, string $message, string $color = 'info'): array;

    /**
     * Check if the channel is enabled
     */
    public function isEnabled(): bool
    {
        return ($this->config['enabled'] ?? false) && ! empty($this->getWebhookUrl());
    }

    /**
     * Send a notification for monitor uptime change
     */
    public function sendUptimeNotification(Monitor $monitor, string $status): void
    {
        $title = "Monitor {$status}: {$monitor->url}";
        $message = "Monitor `{$monitor->url}` is now **{$status}**";
        $color = $status === 'up' ? 'success' : 'danger';

        $this->send($title, $message, $color);
    }

    /**
     * Send a notification for SSL status change
     */
    public function sendSslNotification(Monitor $monitor, string $status): void
    {
        $title = "SSL Status Changed: {$monitor->url}";
        $message = "SSL certificate for `{$monitor->url}` is now **{$status}**";
        $color = in_array($status, ['valid', 'renewed']) ? 'success' : 'warning';

        $this->send($title, $message, $color);
    }

    /**
     * Send a notification for performance degradation
     */
    public function sendPerformanceDegradedNotification(Monitor $monitor, float $responseTime, float $threshold): void
    {
        $title = "Performance Degraded: {$monitor->url}";
        $message = "Monitor `{$monitor->url}` response time (**".number_format($responseTime, 2).'ms**) exceeded threshold (**'.number_format($threshold, 2).'ms**)';
        $color = 'warning';

        $this->send($title, $message, $color);
    }

    /**
     * Send the notification
     */
    protected function send(string $title, string $message, string $color = 'info'): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $webhookUrl = $this->getWebhookUrl();
        if (! $webhookUrl) {
            return;
        }

        try {
            Http::post($webhookUrl, $this->formatPayload($title, $message, $color));
        } catch (\Exception $e) {
            logger()->error('Failed to send notification', [
                'channel' => static::class,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
