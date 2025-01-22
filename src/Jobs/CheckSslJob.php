<?php

namespace CleaniqueCoders\AppPulse\Jobs;

use CleaniqueCoders\AppPulse\Actions\CheckSsl;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Job CheckSslJob
 *
 * This job is responsible for initiating the SSL check process for a specified monitor instance.
 * It dispatches the SSL check action asynchronously through Laravel's queue system.
 */
class CheckSslJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    /**
     * The monitor instance to be checked.
     */
    public Monitor $monitor;

    /**
     * Create a new job instance.
     *
     * @param  Monitor  $monitor  The monitor model instance containing details for the SSL check.
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
        (new CheckSsl($this->monitor))->execute();
    }
}
