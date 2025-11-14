<?php

namespace CleaniqueCoders\AppPulse\Actions;

use CleaniqueCoders\AppPulse\Enums\SiteStatus;
use CleaniqueCoders\AppPulse\Enums\Type;
use CleaniqueCoders\AppPulse\Events\MonitorResponseTimeDegraded;
use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use CleaniqueCoders\AppPulse\Models\Monitor;
use CleaniqueCoders\AppPulse\Models\MonitorHistory;
use CleaniqueCoders\Traitify\Contracts\Execute;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckMonitor implements Execute
{
    public function __construct(protected Monitor $monitor) {}

    public function execute(): self
    {
        // Skip check if monitor is in maintenance mode
        if ($this->monitor->isInMaintenance()) {
            return $this;
        }

        $monitor = $this->monitor;
        $status = SiteStatus::tryFrom($monitor->status);
        $error_message = null;
        $response_time = 0;
        $retry_count = 0;
        $timeout = $monitor->timeout ?? 10;
        $max_retries = $monitor->retry_attempts ?? 3;
        $retry_delay = $monitor->retry_delay ?? 1;

        // Attempt the check with retry logic
        $attempt = 0;
        while ($attempt < $max_retries) {
            try {
                $startTime = microtime(true);
                $response = Http::timeout($timeout)->get($monitor->url);
                $status = $response->ok() ? SiteStatus::UP : SiteStatus::DOWN;
                $response_time = (microtime(true) - $startTime) * 1000;

                // If successful (UP), break out of retry loop
                if ($status === SiteStatus::UP) {
                    break;
                }

                // If response is not ok but didn't throw exception, record it
                $error_message = 'HTTP Status: '.$response->status();
            } catch (\Exception $e) {
                $status = SiteStatus::DOWN;
                $error_message = $e->getMessage();
                $response_time = (microtime(true) - $startTime) * 1000;
            }

            $attempt++;
            $retry_count = $attempt;

            // If we haven't exhausted retries, wait before next attempt with exponential backoff
            if ($attempt < $max_retries && $status === SiteStatus::DOWN) {
                $delay = $retry_delay * (2 ** ($attempt - 1)); // Exponential backoff
                sleep($delay);
            }
        }

        // Create history record
        $this->createHistory($status, $response_time, $error_message, $retry_count);

        // Check for performance degradation
        if ($status === SiteStatus::UP && $monitor->isResponseTimeDegraded($response_time)) {
            if (! $monitor->shouldThrottleAlert()) {
                MonitorResponseTimeDegraded::dispatch($monitor, $response_time, $monitor->response_time_threshold);
                $monitor->markAlertSent();
            }
        }

        // Update last checked timestamp
        $monitor->update(['last_checked_at' => now()]);

        return $this;
    }

    protected function createHistory(
        SiteStatus $status,
        float $response_time,
        ?string $error_message,
        int $retry_count
    ): void {
        $monitor = $this->monitor;

        // if no record, just create the history
        if (! $this->monitor->hasHistory(Type::UPTIME)) {
            MonitorHistory::create([
                'uuid' => Str::orderedUuid(),
                'monitor_id' => $monitor->id,
                'type' => Type::UPTIME->value,
                'status' => $status->value,
                'response_time' => $response_time,
                'error_message' => $error_message,
                'retry_count' => $retry_count,
            ]);

            if (! $monitor->shouldThrottleAlert()) {
                MonitorUptimeChanged::dispatch($this->monitor, $status);
                $monitor->markAlertSent();
            }

            return;
        }

        // if there's histories, get latest history and
        // and compare with the current status.
        $previous_history = $this->monitor->getLatestHistory(Type::UPTIME);
        $previous_status = SiteStatus::tryFrom($previous_history->status);

        MonitorHistory::create([
            'uuid' => Str::orderedUuid(),
            'monitor_id' => $monitor->id,
            'type' => Type::UPTIME->value,
            'status' => $status->value,
            'response_time' => $response_time,
            'error_message' => $error_message,
            'retry_count' => $retry_count,
        ]);

        if ($previous_status && $status->value != $previous_status?->value) {
            if (! $monitor->shouldThrottleAlert()) {
                MonitorUptimeChanged::dispatch($this->monitor, $status);
                $monitor->markAlertSent();
            }
        }
    }
}
