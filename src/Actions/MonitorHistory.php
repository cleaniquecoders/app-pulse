<?php

namespace CleaniqueCoders\AppPulse\Actions;

use CleaniqueCoders\AppPulse\Models\MonitorHistory as Model;

/**
 * Class MonitorHistory
 *
 * Provides a method to create a MonitorHistory record with specified data.
 */
class MonitorHistory
{
    /**
     * Create a new MonitorHistory instance with specified data.
     *
     * @param array{
     *     uuid: string,
     *     monitor_id: int,
     *     type: string,
     *     status: string,
     *     response_time: int|float,
     *     error_message?: string|null
     * } $data Data required to create a new MonitorHistory instance.
     */
    public static function create(array $data): Model
    {
        return Model::create($data);
    }
}
