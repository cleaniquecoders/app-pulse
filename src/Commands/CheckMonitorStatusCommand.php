<?php

namespace CleaniqueCoders\AppPulse\Commands;

use CleaniqueCoders\AppPulse\Jobs\CheckMonitorJob;
use CleaniqueCoders\AppPulse\Jobs\CheckSslJob;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Console\Command;

class CheckMonitorStatusCommand extends Command
{
    protected $signature = 'monitor:check-status
                            {--chunk-size=100 : The number of monitors to process per chunk}
                            {--queue=default : The queue to use for dispatching the jobs}
                            {--force-check-ssl : Force SSL checks regardless of monitor settings}';

    protected $description = 'Check the status and SSL validity of all monitors';

    public function handle(): void
    {
        $chunkSize = (int) $this->option('chunk-size');
        $queueName = $this->option('queue');
        $forceCheckSsl = (bool) $this->option('force-check-ssl');

        Monitor::chunk($chunkSize, function ($monitors) use ($queueName, $forceCheckSsl) {
            foreach ($monitors as $monitor) {
                // Dispatch CheckMonitor job to the specified queue
                CheckMonitorJob::dispatch($monitor)->onQueue($queueName);

                // Conditionally dispatch CheckSsl job if SSL check is enabled or forced
                if ($monitor->ssl_check || $forceCheckSsl) {
                    CheckSslJob::dispatch($monitor)->onQueue($queueName);
                }
            }
        });

        $this->components->info("Monitor checks have been dispatched to the '{$queueName}' queue.");
    }
}
