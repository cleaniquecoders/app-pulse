<?php

use CleaniqueCoders\AppPulse\Events\MonitorResponseTimeDegraded;
use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use CleaniqueCoders\AppPulse\Events\SslStatusChanged;
use CleaniqueCoders\AppPulse\Listeners\SendPerformanceDegradedNotification;
use CleaniqueCoders\AppPulse\Listeners\SendSslNotification;
use CleaniqueCoders\AppPulse\Listeners\SendUptimeNotification;

return [
    'events' => [
        MonitorUptimeChanged::class => [
            SendUptimeNotification::class,
        ],
        SslStatusChanged::class => [
            SendSslNotification::class,
        ],
        MonitorResponseTimeDegraded::class => [
            SendPerformanceDegradedNotification::class,
        ],
    ],

    'scheduler' => [
        'interval' => env('APP_PULSE_SCHEDULER_INTERVAL', 1),
        'queue' => env('APP_PULSE_SCHEDULER_QUEUE', 'default'),
        'chunk' => env('APP_PULSE_SCHEDULER_CHUNK', 100),
    ],

    'defaults' => [
        'timeout' => env('APP_PULSE_DEFAULT_TIMEOUT', 10), // seconds
        'retry_attempts' => env('APP_PULSE_DEFAULT_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('APP_PULSE_DEFAULT_RETRY_DELAY', 1), // seconds
        'alert_throttle_minutes' => env('APP_PULSE_DEFAULT_ALERT_THROTTLE', 60),
    ],

    'notifications' => [
        'enabled' => env('APP_PULSE_NOTIFICATIONS_ENABLED', true),
        'channels' => [
            'slack' => [
                'enabled' => env('APP_PULSE_SLACK_ENABLED', false),
                'webhook_url' => env('APP_PULSE_SLACK_WEBHOOK_URL'),
            ],
            'discord' => [
                'enabled' => env('APP_PULSE_DISCORD_ENABLED', false),
                'webhook_url' => env('APP_PULSE_DISCORD_WEBHOOK_URL'),
            ],
            'teams' => [
                'enabled' => env('APP_PULSE_TEAMS_ENABLED', false),
                'webhook_url' => env('APP_PULSE_TEAMS_WEBHOOK_URL'),
            ],
            'webhook' => [
                'enabled' => env('APP_PULSE_WEBHOOK_ENABLED', false),
                'url' => env('APP_PULSE_WEBHOOK_URL'),
            ],
        ],
    ],

];
