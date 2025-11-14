<?php

namespace CleaniqueCoders\AppPulse\Events;

use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MonitorResponseTimeDegraded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Monitor $monitor,
        public float $responseTime,
        public float $threshold
    ) {}
}
