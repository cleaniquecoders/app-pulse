<?php

use CleaniqueCoders\AppPulse\Models\Monitor;

it('detects when monitor is in maintenance mode without time window', function () {
    $monitor = Monitor::factory()->create([
        'is_maintenance' => true,
        'maintenance_start_at' => null,
        'maintenance_end_at' => null,
    ]);

    expect($monitor->isInMaintenance())->toBeTrue();
});

it('detects when monitor is in maintenance mode within time window', function () {
    $monitor = Monitor::factory()->create([
        'is_maintenance' => true,
        'maintenance_start_at' => now()->subHour(),
        'maintenance_end_at' => now()->addHour(),
    ]);

    expect($monitor->isInMaintenance())->toBeTrue();
});

it('detects when monitor is not in maintenance mode outside time window', function () {
    $monitor = Monitor::factory()->create([
        'is_maintenance' => true,
        'maintenance_start_at' => now()->addHour(),
        'maintenance_end_at' => now()->addHours(2),
    ]);

    expect($monitor->isInMaintenance())->toBeFalse();
});

it('detects when monitor is not in maintenance mode when flag is false', function () {
    $monitor = Monitor::factory()->create([
        'is_maintenance' => false,
        'maintenance_start_at' => now()->subHour(),
        'maintenance_end_at' => now()->addHour(),
    ]);

    expect($monitor->isInMaintenance())->toBeFalse();
});

it('detects when maintenance has ended', function () {
    $monitor = Monitor::factory()->create([
        'is_maintenance' => true,
        'maintenance_start_at' => now()->subHours(2),
        'maintenance_end_at' => now()->subHour(),
    ]);

    expect($monitor->isInMaintenance())->toBeFalse();
});
