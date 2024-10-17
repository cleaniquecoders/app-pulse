<?php

namespace CleaniqueCoders\AppPulse;

use CleaniqueCoders\AppPulse\Commands\CheckMonitorStatusCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AppPulseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('app-pulse')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_app_pulse_table')
            ->hasCommand(CheckMonitorStatusCommand::class);
    }

    public function packageBooted()
    {
        $events = config('app-pulse.events', []);

        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->app['events']->listen($event, $listener);
            }
        }
    }
}
