<?php

namespace CleaniqueCoders\AppPulse\Commands;

use CleaniqueCoders\AppPulse\Enums\Status;
use CleaniqueCoders\AppPulse\Enums\Type;
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

        Monitor::where('status', Status::ENABLED->value)->chunk($chunkSize, function ($monitors) use ($queueName, $forceCheckSsl) {
            foreach ($monitors as $monitor) {
                // first time, dispatch immediately
                if (! $monitor->hasHistory(Type::SSL) && ! $monitor->hasHistory(Type::UPTIME)) {
                    $this->dispatchJobs($monitor, $queueName, $forceCheckSsl);

                    continue;
                }

                // Get the latest history record - we don't care either it's SSL / UPTIME Check.
                $lastHistory = $monitor->histories()->latest('created_at')->first();

                // Calculate time difference in minutes
                $timeSinceLastCheck = $lastHistory
                    ? $lastHistory->created_at->diffInSeconds(now())
                    : $monitor->interval; // If no history exists, run immediately

                // Dispatch only if the interval condition is met
                if ($timeSinceLastCheck >= $monitor->interval * 60) {
                    $this->dispatchJobs($monitor, $queueName, $forceCheckSsl);
                }
            }
        });

        $this->components->info("Monitor checks have been dispatched to the '{$queueName}' queue.");
    }

    private function dispatchJobs(Monitor $monitor, string $queueName, bool $forceCheckSsl)
    {
        CheckMonitorJob::dispatch($monitor)->onQueue($queueName);

        // Conditionally dispatch CheckSsl job
        if ($monitor->ssl_check || $forceCheckSsl) {
            CheckSslJob::dispatch($monitor)->onQueue($queueName);
        }
    }
}
