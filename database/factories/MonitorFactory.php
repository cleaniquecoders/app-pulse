<?php

namespace CleaniqueCoders\AppPulse\Database\Factories;

use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

class MonitorFactory extends Factory
{
    protected $model = Monitor::class;

    public function definition()
    {
        $owner = new class extends Model
        {
            protected $table = 'users';

            public $id = 1;
        };

        return [
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
            'url' => fake()->url,
            'status' => false,
            'interval' => fake()->numberBetween(1, 60),
            'timeout' => 10,
            'retry_attempts' => 3,
            'retry_delay' => 1,
            'response_time_threshold' => null,
            'ssl_check' => true,
            'is_maintenance' => false,
            'maintenance_start_at' => null,
            'maintenance_end_at' => null,
            'alert_throttle_minutes' => 60,
            'last_alerted_at' => null,
            'notification_channels' => null,
        ];
    }
}
