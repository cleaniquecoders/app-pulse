<?php

use CleaniqueCoders\AppPulse\Actions\ToggleMonitoring;
use CleaniqueCoders\AppPulse\Enums\Status;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test monitor
    $this->monitor = Monitor::factory()->create([
        'status' => Status::ENABLED->value, // Initially enabled
    ]);
});

it('disables monitoring if it is currently enabled', function () {
    // Ensure initial status is enabled
    expect($this->monitor->status)->toBe(true);

    // Execute the action
    (new ToggleMonitoring($this->monitor))->execute();

    // Refresh the monitor instance and check status
    $this->monitor->refresh();
    expect($this->monitor->status)->toBe(false);
});

it('enables monitoring if it is currently disabled', function () {
    // Update the monitor to disabled
    $this->monitor->update(['status' => Status::DISABLED->value]);

    // Ensure initial status is disabled
    expect($this->monitor->status)->toBe(false);

    // Execute the action
    (new ToggleMonitoring($this->monitor))->execute();

    // Refresh the monitor instance and check status
    $this->monitor->refresh();
    expect($this->monitor->status)->toBe(true);
});
