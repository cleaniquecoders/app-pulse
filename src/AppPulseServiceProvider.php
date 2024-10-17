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

        $schedule = $this->app->make(Schedule::class);

        $interval = config('app-pulse.scheduler.interval');
        $queue = config('app-pulse.scheduler.queue', 'default');
        $chunk = config('app-pulse.scheduler.chunk');

        $schedule->command("monitor:check-status --queue={$queue} --chunk={$chunk}")
                 ->everyMinute()
                 ->when(function () use ($interval) {
                     // Only run if the interval matches
                     return now()->minute % $interval === 0;
                 });
    }
}
