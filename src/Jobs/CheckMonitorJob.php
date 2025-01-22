<?php

namespace CleaniqueCoders\AppPulse\Jobs;

use CleaniqueCoders\AppPulse\Actions\CheckMonitor;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Job CheckMonitorJob
 *
 * This job is responsible for performing a monitoring check on a specified monitor instance.
 * It dispatches the monitor check action asynchronously via Laravel's queue system.
 */
class CheckMonitorJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    /**
     * The monitor instance to be checked.
     */
    public Monitor $monitor;

    /**
     * Create a new job instance.
     *
     * @param  Monitor  $monitor  The monitor model instance containing details for the check.
     */
    public function __construct(Monitor $monitor)
    {
        $this->monitor = $monitor;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new CheckMonitor($this->monitor))->execute();
    }
}
