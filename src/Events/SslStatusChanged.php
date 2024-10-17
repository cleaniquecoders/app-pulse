<?php

namespace CleaniqueCoders\AppPulse\Events;

use CleaniqueCoders\AppPulse\Enums\SslStatus;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SslStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Monitor $monitor,
        public SslStatus $status
    ) {}
}
