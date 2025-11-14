<?php

namespace CleaniqueCoders\AppPulse\Notifications;

use CleaniqueCoders\AppPulse\Contracts\NotificationChannel;
use CleaniqueCoders\AppPulse\Models\Monitor;
use CleaniqueCoders\AppPulse\Notifications\Channels\DiscordChannel;
use CleaniqueCoders\AppPulse\Notifications\Channels\SlackChannel;
use CleaniqueCoders\AppPulse\Notifications\Channels\TeamsChannel;
use CleaniqueCoders\AppPulse\Notifications\Channels\WebhookChannel;

class NotificationManager
{
    /**
     * @var array<NotificationChannel>
     */
    protected array $channels = [];

    public function __construct()
    {
        $this->initializeChannels();
    }

    /**
     * Initialize notification channels from config
     */
    protected function initializeChannels(): void
    {
        if (! config('app-pulse.notifications.enabled', true)) {
            return;
        }

        $channels = config('app-pulse.notifications.channels', []);

        if (isset($channels['slack'])) {
            $this->channels[] = new SlackChannel($channels['slack']);
        }

        if (isset($channels['discord'])) {
            $this->channels[] = new DiscordChannel($channels['discord']);
        }

        if (isset($channels['teams'])) {
            $this->channels[] = new TeamsChannel($channels['teams']);
        }

        if (isset($channels['webhook'])) {
            $this->channels[] = new WebhookChannel($channels['webhook']);
        }
    }

    /**
     * Add a custom notification channel
     */
    public function addChannel(NotificationChannel $channel): self
    {
        $this->channels[] = $channel;

        return $this;
    }

    /**
     * Send uptime notification through all enabled channels
     */
    public function sendUptimeNotification(Monitor $monitor, string $status): void
    {
        foreach ($this->getEnabledChannels() as $channel) {
            $channel->sendUptimeNotification($monitor, $status);
        }
    }

    /**
     * Send SSL notification through all enabled channels
     */
    public function sendSslNotification(Monitor $monitor, string $status): void
    {
        foreach ($this->getEnabledChannels() as $channel) {
            $channel->sendSslNotification($monitor, $status);
        }
    }

    /**
     * Send performance degraded notification through all enabled channels
     */
    public function sendPerformanceDegradedNotification(Monitor $monitor, float $responseTime, float $threshold): void
    {
        foreach ($this->getEnabledChannels() as $channel) {
            $channel->sendPerformanceDegradedNotification($monitor, $responseTime, $threshold);
        }
    }

    /**
     * Get only enabled channels
     *
     * @return array<NotificationChannel>
     */
    protected function getEnabledChannels(): array
    {
        return array_filter($this->channels, fn ($channel) => $channel->isEnabled());
    }

    /**
     * Get all channels
     *
     * @return array<NotificationChannel>
     */
    public function getChannels(): array
    {
        return $this->channels;
    }
}
