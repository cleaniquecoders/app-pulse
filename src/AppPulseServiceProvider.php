<?php

namespace CleaniqueCoders\AppPulse;

use CleaniqueCoders\AppPulse\Commands\CheckMonitorStatusCommand;
use CleaniqueCoders\AppPulse\Notifications\NotificationManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AppPulseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('app-pulse')
            ->hasConfigFile()
            ->hasMigrations(
                'create_app_pulse_table',
                'update_monitor_status_data_type',
                'add_enhanced_monitoring_and_alerting_features_to_monitors_table'
            )
            ->hasCommand(CheckMonitorStatusCommand::class);
    }

    public function packageRegistered(): void
    {
        // Bind NotificationManager as a singleton
        $this->app->singleton(NotificationManager::class, function ($app) {
            return new NotificationManager;
        });
    }

    public function packageBooted(): void
    {
        $events = config('app-pulse.events', []);

        if (is_array($events)) {
            /** @var Dispatcher $dispatcher */
            $dispatcher = $this->app->make(Dispatcher::class);

            foreach ($events as $event => $listeners) {
                if (is_array($listeners)) {
                    foreach ($listeners as $listener) {
                        $dispatcher->listen($event, $listener);
                    }
                }
            }
        }

        $schedule = $this->app->make(Schedule::class);

        /** @var int */
        $interval = config('app-pulse.scheduler.interval') ?? 1;
        /** @var string */
        $queue = config('app-pulse.scheduler.queue') ?? 'default';
        /** @var int */
        $chunk = config('app-pulse.scheduler.chunk') ?? 100;

        $schedule->command("monitor:check-status --queue=$queue --chunk=$chunk")
            ->everyMinute()
            ->when(function () use ($interval) {
                return now()->minute % $interval === 0;
            });
    }
}
