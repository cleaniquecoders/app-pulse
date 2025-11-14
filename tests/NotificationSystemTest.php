<?php

use CleaniqueCoders\AppPulse\Notifications\Channels\DiscordChannel;
use CleaniqueCoders\AppPulse\Notifications\Channels\SlackChannel;
use CleaniqueCoders\AppPulse\Notifications\Channels\TeamsChannel;
use CleaniqueCoders\AppPulse\Notifications\Channels\WebhookChannel;
use CleaniqueCoders\AppPulse\Notifications\NotificationManager;

it('slack channel is enabled when config is correct', function () {
    $channel = new SlackChannel([
        'enabled' => true,
        'webhook_url' => 'https://hooks.slack.com/test',
    ]);

    expect($channel->isEnabled())->toBeTrue();
});

it('slack channel is disabled when webhook is missing', function () {
    $channel = new SlackChannel([
        'enabled' => true,
        'webhook_url' => null,
    ]);

    expect($channel->isEnabled())->toBeFalse();
});

it('discord channel is enabled when config is correct', function () {
    $channel = new DiscordChannel([
        'enabled' => true,
        'webhook_url' => 'https://discord.com/api/webhooks/test',
    ]);

    expect($channel->isEnabled())->toBeTrue();
});

it('teams channel is enabled when config is correct', function () {
    $channel = new TeamsChannel([
        'enabled' => true,
        'webhook_url' => 'https://outlook.office.com/webhook/test',
    ]);

    expect($channel->isEnabled())->toBeTrue();
});

it('webhook channel is enabled when config is correct', function () {
    $channel = new WebhookChannel([
        'enabled' => true,
        'url' => 'https://example.com/webhook',
    ]);

    expect($channel->isEnabled())->toBeTrue();
});

it('notification manager can add custom channels', function () {
    config(['app-pulse.notifications.channels' => []]);

    $manager = new NotificationManager;

    $customChannel = new SlackChannel([
        'enabled' => true,
        'webhook_url' => 'https://hooks.slack.com/custom',
    ]);

    $manager->addChannel($customChannel);

    expect($manager->getChannels())->toHaveCount(1);
});

it('notification manager initializes channels from config', function () {
    config([
        'app-pulse.notifications.enabled' => true,
        'app-pulse.notifications.channels' => [
            'slack' => [
                'enabled' => true,
                'webhook_url' => 'https://hooks.slack.com/test',
            ],
            'discord' => [
                'enabled' => true,
                'webhook_url' => 'https://discord.com/api/webhooks/test',
            ],
        ],
    ]);

    $manager = new NotificationManager;

    expect($manager->getChannels())->toHaveCount(2);
});

it('slack channel formats payload correctly', function () {
    $channel = new SlackChannel([
        'enabled' => true,
        'webhook_url' => 'https://hooks.slack.com/test',
    ]);

    $reflection = new ReflectionClass($channel);
    $method = $reflection->getMethod('formatPayload');
    $method->setAccessible(true);

    $payload = $method->invoke($channel, 'Test Title', 'Test Message', 'success');

    expect($payload)->toHaveKey('attachments');
    expect($payload['attachments'][0])->toHaveKey('color');
    expect($payload['attachments'][0])->toHaveKey('title', 'Test Title');
    expect($payload['attachments'][0])->toHaveKey('text', 'Test Message');
});

it('discord channel formats payload correctly', function () {
    $channel = new DiscordChannel([
        'enabled' => true,
        'webhook_url' => 'https://discord.com/api/webhooks/test',
    ]);

    $reflection = new ReflectionClass($channel);
    $method = $reflection->getMethod('formatPayload');
    $method->setAccessible(true);

    $payload = $method->invoke($channel, 'Test Title', 'Test Message', 'danger');

    expect($payload)->toHaveKey('embeds');
    expect($payload['embeds'][0])->toHaveKey('title', 'Test Title');
    expect($payload['embeds'][0])->toHaveKey('description', 'Test Message');
    expect($payload['embeds'][0])->toHaveKey('color');
});

it('webhook channel formats payload correctly', function () {
    $channel = new WebhookChannel([
        'enabled' => true,
        'url' => 'https://example.com/webhook',
    ]);

    $reflection = new ReflectionClass($channel);
    $method = $reflection->getMethod('formatPayload');
    $method->setAccessible(true);

    $payload = $method->invoke($channel, 'Test Title', 'Test Message', 'warning');

    expect($payload)->toHaveKey('event', 'monitor_alert');
    expect($payload)->toHaveKey('title', 'Test Title');
    expect($payload)->toHaveKey('message', 'Test Message');
    expect($payload)->toHaveKey('severity', 'warning');
    expect($payload)->toHaveKey('source', 'AppPulse');
});
