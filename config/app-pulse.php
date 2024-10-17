<?php

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use CleaniqueCoders\AppPulse\Events\SslStatusChanged;

return [
    'events' => [
        MonitorUptimeChanged::class => [],
        SslStatusChanged::class => [],
    ],
];
