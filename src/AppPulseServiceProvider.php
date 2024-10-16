<?php

namespace CleaniqueCoders\AppPulse;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use CleaniqueCoders\AppPulse\Commands\AppPulseCommand;

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
            ->hasCommand(AppPulseCommand::class);
    }
}
