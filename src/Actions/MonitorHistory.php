<?php

namespace CleaniqueCoders\AppPulse\Actions;

use CleaniqueCoders\AppPulse\Models\MonitorHistory as Model;

class MonitorHistory
{
    public static function create(array $data): Model
    {
        return Model::create($data);
    }
}
