<?php

namespace CleaniqueCoders\AppPulse\Events;

use CleaniqueCoders\AppPulse\Enums\SiteStatus;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MonitorUptimeChanged
{
    use Dispatchable, SerializesModels;

    public Monitor $monitor;

    public SiteStatus $status;

    public function __construct(Monitor $monitor, SiteStatus $status)
    {
        $this->monitor = $monitor;
        $this->status = $status;
    }
}
