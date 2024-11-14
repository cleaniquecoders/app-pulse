<?php

namespace CleaniqueCoders\AppPulse;

use CleaniqueCoders\AppPulse\Commands\CheckMonitorStatusCommand;
use Illuminate\Console\Scheduling\Schedule;
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
            ->hasMigration('create_app_pulse_table')
            ->hasCommand(CheckMonitorStatusCommand::class);
    }

    /**
     * Boot additional package functionality, such as event listeners and scheduled tasks.
     */
    public function packageBooted(): void
    {
        $events = config('app-pulse.events', []);

        if (is_array($events)) {
            foreach ($events as $event => $listeners) {
                if (is_array($listeners)) {
                    foreach ($listeners as $listener) {
                        $this->app['events']->listen($event, $listener);
                    }
                }
            }
        }

        $schedule = $this->app->make(Schedule::class);

        $interval = config('app-pulse.scheduler.interval', 1);
        $queue = config('app-pulse.scheduler.queue', 'default');
        $chunk = config('app-pulse.scheduler.chunk', 100);

        $schedule->command("monitor:check-status --queue={$queue} --chunk={$chunk}")
            ->everyMinute()
            ->when(function () use ($interval) {
                // Only run if the interval matches
                return now()->minute % $interval === 0;
            });
    }
}
