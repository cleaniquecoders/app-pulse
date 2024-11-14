<?php

namespace CleaniqueCoders\AppPulse\Contracts;

use CleaniqueCoders\AppPulse\Models\Monitor as Model;

/**
 * Contract Monitor
 *
 * Provides a contract for managing Monitor instances with create, update, and delete operations.
 */
interface Monitor
{
    /**
     * Create a new Monitor instance with specified data.
     *
     * @param  array{uuid?: string, owner_id: int, owner_type: string, url: string, status?: string, interval?: int, ssl_check?: bool, last_checked_at?: \DateTime|string|null}  $data
     */
    public function create(array $data): Model;

    /**
     * Update an existing Monitor instance with specified data.
     *
     * @param  Model  $monitor  The monitor to update.
     * @param  array{uuid?: string, owner_id?: int, owner_type?: string, url?: string, status?: string, interval?: int, ssl_check?: bool, last_checked_at?: \DateTime|string|null}  $data
     */
    public function update(Model $monitor, array $data): Model;

    /**
     * Delete a Monitor instance.
     *
     * @param  Model  $monitor  The monitor to delete.
     */
    public function delete(Model $monitor): bool;
}
