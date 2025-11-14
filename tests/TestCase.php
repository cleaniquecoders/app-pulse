<?php

namespace CleaniqueCoders\AppPulse\Tests;

use CleaniqueCoders\AppPulse\AppPulseServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'CleaniqueCoders\\AppPulse\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            AppPulseServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/create_app_pulse_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/update_monitor_status_data_type.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/add_enhanced_monitoring_and_alerting_features_to_monitors_table.php.stub';
        $migration->up();
    }
}
