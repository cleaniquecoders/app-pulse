<?php

namespace CleaniqueCoders\AppPulse\Notifications\Channels;

class WebhookChannel extends AbstractNotificationChannel
{
    protected function getWebhookUrl(): ?string
    {
        return $this->config['url'] ?? null;
    }

    protected function formatPayload(string $title, string $message, string $color = 'info'): array
    {
        return [
            'event' => 'monitor_alert',
            'title' => $title,
            'message' => $message,
            'severity' => $color,
            'timestamp' => now()->toIso8601String(),
            'source' => 'AppPulse',
        ];
    }
}
