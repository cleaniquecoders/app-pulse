<?php

namespace CleaniqueCoders\AppPulse\Notifications\Channels;

class SlackChannel extends AbstractNotificationChannel
{
    protected function getWebhookUrl(): ?string
    {
        return $this->config['webhook_url'] ?? null;
    }

    protected function formatPayload(string $title, string $message, string $color = 'info'): array
    {
        $colorMap = [
            'success' => 'good',
            'danger' => 'danger',
            'warning' => 'warning',
            'info' => '#36a64f',
        ];

        return [
            'attachments' => [
                [
                    'color' => $colorMap[$color] ?? $colorMap['info'],
                    'title' => $title,
                    'text' => $message,
                    'footer' => 'AppPulse',
                    'footer_icon' => 'https://platform.slack-edge.com/img/default_application_icon.png',
                    'ts' => time(),
                ],
            ],
        ];
    }
}
