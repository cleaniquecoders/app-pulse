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
                            {--force-check-ssl : The queue to use for dispatching the jobs}';

    protected $description = 'Check the status and SSL validity of all monitors';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk-size');
        $queueName = $this->option('queue');

        Monitor::chunk($chunkSize, function ($monitors) use ($queueName) {
            foreach ($monitors as $monitor) {
                // Dispatch CheckMonitor job to the specified queue
                CheckMonitorJob::dispatch($monitor)->onQueue($queueName);

                // If SSL check is enabled or option force-check-ssl is true, dispatch CheckSsl job to the specified queue
                CheckSslJob::dispatchIf(
                    $monitor->ssl_check || $this->option('force-check-ssl'),
                    $monitor
                )->onQueue($queueName);

            }
        });

        $this->components->info("Monitor checks have been dispatched to the '{$queueName}' queue.");
    }
}
