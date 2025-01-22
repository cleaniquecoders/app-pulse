<?php

use CleaniqueCoders\AppPulse\Enums\Status;
use CleaniqueCoders\AppPulse\Enums\Type;
use CleaniqueCoders\AppPulse\Jobs\CheckMonitorJob;
use CleaniqueCoders\AppPulse\Jobs\CheckSslJob;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;

beforeEach(function () {
    // Fake the job dispatch
    Bus::fake();

    // Create monitors with different configurations
    $this->enabledMonitor = Monitor::factory()->create([
        'url' => 'https://example1.com',
        'status' => Status::ENABLED->value,
        'interval' => 10,
        'ssl_check' => true,
    ]);

    $this->disabledMonitor = Monitor::factory()->create([
        'url' => 'https://example2.com',
        'status' => Status::DISABLED->value,
        'interval' => 5,
        'ssl_check' => false,
    ]);
});

it('dispatches jobs for a monitor without history', function () {
    // Ensure no history exists for the disabled monitor
    expect($this->enabledMonitor->histories()->count())->toBe(0);

    // Run the command
    Artisan::call('monitor:check-status', ['--chunk-size' => 100, '--queue' => 'default']);

    // Assert jobs are dispatched immediately for monitors without history
    Bus::assertDispatched(CheckMonitorJob::class, function ($job) {
        return $job->monitor->is($this->enabledMonitor);
    });

    // Ensure jobs are not dispatched for enabled monitors
    Bus::assertNotDispatched(CheckMonitorJob::class, function ($job) {
        return $job->monitor->is($this->disabledMonitor);
    });
});

it('dispatches jobs for monitors meeting interval requirements', function () {
    // Travel back to 11 minutes ago
    $this->travel(-15)->minutes();

    // Simulate the last history for the disabled monitor (interval = 5, last checked 11 minutes ago)
    $this->enabledMonitor->histories()->create([
        'type' => Type::UPTIME->value,
        'status' => 'up',
    ]);

    // then return back
    $this->travelBack();

    // Run the command
    Artisan::call('monitor:check-status', ['--chunk-size' => 100, '--queue' => 'default']);

    // Assert jobs are dispatched for this monitor
    Bus::assertDispatched(CheckMonitorJob::class, function ($job) {
        return $job->monitor->is($this->enabledMonitor);
    });
});

it('does not dispatch jobs for monitors that do not meet interval requirements', function () {
    // Simulate the last history for the disabled monitor (interval = 5, last checked 3 minutes ago)
    $this->disabledMonitor->histories()->create([
        'type' => Type::UPTIME->value,
        'status' => 'up',
        'created_at' => now()->subMinutes(3),
    ]);

    // Run the command
    Artisan::call('monitor:check-status', ['--chunk-size' => 100, '--queue' => 'default']);

    // Ensure jobs are not dispatched for monitors that do not meet interval requirements
    Bus::assertNotDispatched(CheckMonitorJob::class, function ($job) {
        return $job->monitor->is($this->disabledMonitor);
    });
});

it('forces SSL checks regardless of settings when --force-check-ssl is used', function () {
    // Add a recent history for the disabled monitor (would normally skip)
    $this->enabledMonitor->histories()->create([
        'type' => Type::UPTIME->value,
        'status' => 'up',
        'created_at' => now()->subMinutes(2), // Within interval
    ]);

    // Run the command with --force-check-ssl
    Artisan::call('monitor:check-status', ['--force-check-ssl' => true]);

    // Ensure SSL jobs are not dispatched for enabled monitors
    Bus::assertNotDispatched(CheckSslJob::class, function ($job) {
        return $job->monitor->is($this->enabledMonitor);
    });
});
