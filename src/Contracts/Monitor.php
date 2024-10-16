<?php

namespace CleaniqueCoders\AppPulse\Contracts;

use CleaniqueCoders\AppPulse\Models\Monitor as Model;

interface Monitor
{
    public function create(array $data): Model;

    public function update(Model $monitor, array $data): Model;

    public function delete(Model $monitor): bool;
}
