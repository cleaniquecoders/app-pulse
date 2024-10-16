<?php

namespace CleaniqueCoders\AppPulse;

use CleaniqueCoders\AppPulse\Commands\AppPulseCommand;
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
            ->hasCommand(AppPulseCommand::class);
    }
}
