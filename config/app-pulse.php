<?php

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use CleaniqueCoders\AppPulse\Events\SslStatusChanged;

return [
    'events' => [
        MonitorUptimeChanged::class => [],
        SslStatusChanged::class => [],
    ],

    'scheduler' => [
        'interval' => env('APP_PULSE_SCHEDULER_INTERVAL', 1),
        'queue' => env('APP_PULSE_SCHEDULER_QUEUE', 'default'),
        'chunk' => env('APP_PULSE_SCHEDULER_CHUNK', 100),
    ],

];
