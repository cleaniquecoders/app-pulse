<?php

namespace CleaniqueCoders\AppPulse\Jobs;

use CleaniqueCoders\AppPulse\Actions\CheckSsl;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckSslJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected Monitor $monitor;

    public function __construct(Monitor $monitor)
    {
        $this->monitor = $monitor;
    }

    public function handle()
    {
        (new CheckSsl($this->monitor))->execute();
    }
}
