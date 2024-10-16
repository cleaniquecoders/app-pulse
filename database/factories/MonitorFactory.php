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
            'status' => 'pending',
            'interval' => fake()->numberBetween(1, 60),
            'ssl_check' => true,
        ];
    }
}
