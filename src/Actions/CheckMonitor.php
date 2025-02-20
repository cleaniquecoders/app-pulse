<?php

namespace CleaniqueCoders\AppPulse\Actions;

use CleaniqueCoders\AppPulse\Enums\SiteStatus;
use CleaniqueCoders\AppPulse\Enums\Type;
use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use CleaniqueCoders\AppPulse\Models\Monitor;
use CleaniqueCoders\Traitify\Contracts\Execute;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckMonitor implements Execute
{
    public function __construct(protected Monitor $monitor) {}

    public function execute(): self
    {
        $monitor = $this->monitor;
        $status = SiteStatus::tryFrom($monitor->status);
        $error_message = null;
        $response_time = 0;
        $startTime = 0;

        try {
            $startTime = microtime(true);
            $response = Http::get($monitor->url);
            $status = $response->ok() ? SiteStatus::UP : SiteStatus::DOWN;
        } catch (\Exception $e) {
            $status = SiteStatus::DOWN;
            $error_message = $e->getMessage();
        } finally {
            $response_time = (microtime(true) - $startTime) * 1000;
        }

        // if no record, just create the history
        if (! $this->monitor->hasHistory(Type::UPTIME)) {
            MonitorHistory::create([
                'uuid' => Str::orderedUuid(),
                'monitor_id' => $monitor->id,
                'type' => Type::UPTIME->value,
                'status' => $status->value,
                'response_time' => $response_time,
                'error_message' => $error_message,
            ]);

            MonitorUptimeChanged::dispatch($this->monitor, $status);

            return $this;
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
        ]);

        if ($previous_status && $status->value != $previous_status?->value) {
            MonitorUptimeChanged::dispatch($this->monitor, $status);
        }

        return $this;
    }
}
