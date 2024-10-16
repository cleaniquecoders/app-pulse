<?php

namespace CleaniqueCoders\AppPulse\Actions;

use CleaniqueCoders\AppPulse\Contracts\Monitor as Contract;
use CleaniqueCoders\AppPulse\Models\Monitor as Model;

class Monitor implements Contract
{
    public function create(array $data): Model
    {
        return Model::create($data);
    }

    public function update(Model $monitor, array $data): Model
    {
        $monitor->update($data);

        return $monitor;
    }

    public function delete(Model $monitor): bool
    {
        return $monitor->delete();
    }
}
