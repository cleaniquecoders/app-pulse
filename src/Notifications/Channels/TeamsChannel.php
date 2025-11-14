<?php

namespace CleaniqueCoders\AppPulse\Notifications\Channels;

class TeamsChannel extends AbstractNotificationChannel
{
    protected function getWebhookUrl(): ?string
    {
        return $this->config['webhook_url'] ?? null;
    }

    protected function formatPayload(string $title, string $message, string $color = 'info'): array
    {
        $colorMap = [
            'success' => '00FF00',
            'danger' => 'FF0000',
            'warning' => 'FFA500',
            'info' => '0078D4',
        ];

        return [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'summary' => $title,
            'themeColor' => $colorMap[$color] ?? $colorMap['info'],
            'title' => $title,
            'sections' => [
                [
                    'activityTitle' => 'AppPulse',
                    'activitySubtitle' => now()->toDateTimeString(),
                    'text' => $message,
                ],
            ],
        ];
    }
}
