<?php

namespace CleaniqueCoders\AppPulse\Notifications\Channels;

class DiscordChannel extends AbstractNotificationChannel
{
    protected function getWebhookUrl(): ?string
    {
        return $this->config['webhook_url'] ?? null;
    }

    protected function formatPayload(string $title, string $message, string $color = 'info'): array
    {
        $colorMap = [
            'success' => 3066993, // Green
            'danger' => 15158332, // Red
            'warning' => 16776960, // Yellow/Orange
            'info' => 3447003, // Blue
        ];

        return [
            'embeds' => [
                [
                    'title' => $title,
                    'description' => $message,
                    'color' => $colorMap[$color] ?? $colorMap['info'],
                    'footer' => [
                        'text' => 'AppPulse',
                    ],
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];
    }
}
