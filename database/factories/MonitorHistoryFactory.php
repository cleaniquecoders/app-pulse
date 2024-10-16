<?php

namespace CleaniqueCoders\AppPulse\Database\Factories;

use CleaniqueCoders\AppPulse\Models\MonitorHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonitorHistoryFactory extends Factory
{
    protected $model = MonitorHistory::class;

    public function definition()
    {
        return [
            'monitor_id' => \CleaniqueCoders\AppPulse\Models\Monitor::factory(),
            'type' => $this->faker->randomElement(['uptime', 'ssl']),
            'status' => $this->faker->randomElement(['up', 'down', 'ssl_valid', 'ssl_expired']),
            'response_time' => $this->faker->numberBetween(100, 5000),
            'error_message' => $this->faker->optional()->sentence,
        ];
    }
}
