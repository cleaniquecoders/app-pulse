<?php

namespace CleaniqueCoders\AppPulse\Actions;

use CleaniqueCoders\AppPulse\Enums\Status;
use CleaniqueCoders\AppPulse\Models\Monitor;
use CleaniqueCoders\Traitify\Contracts\Execute;

class ToggleMonitoring implements Execute
{
    public function __construct(protected Monitor $monitor) {}

    public function execute(): self
    {
        $monitor = $this->monitor;
        $status = Status::tryFrom($monitor->status);

        $monitor->update([
            'status' => $status->value == Status::ENABLED->value ? 0 : 1,
        ]);

        return $this;
    }
}
