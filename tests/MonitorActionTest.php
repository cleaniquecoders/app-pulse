<?php

use CleaniqueCoders\AppPulse\Models\Monitor;
use CleaniqueCoders\AppPulse\Actions\Monitor as MonitorAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->monitorAction = new MonitorAction();
});

it('can create a new monitor', function () {
    $data = [
        'owner_id' => 1,
        'owner_type' => 'App\Models\User',
        'url' => 'https://example.com',
        'interval' => 5,
        'ssl_check' => true,
    ];

    $monitor = $this->monitorAction->create($data);

    expect($monitor)->toBeInstanceOf(Monitor::class);
    expect($monitor->url)->toBe('https://example.com');
    expect($monitor->interval)->toBe(5);
    expect($monitor->ssl_check)->toBeTrue();
});

it('can update an existing monitor', function () {
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com',
        'interval' => 5,
    ]);

    $updatedMonitor = $this->monitorAction->update($monitor, [
        'url' => 'https://new-url.com',
        'interval' => 10,
    ]);

    expect($updatedMonitor->url)->toBe('https://new-url.com');
    expect($updatedMonitor->interval)->toBe(10);
});

it('can delete a monitor', function () {
    $monitor = Monitor::factory()->create();

    $result = $this->monitorAction->delete($monitor);

    expect($result)->toBeTrue();
    expect(Monitor::find($monitor->id))->toBeNull();
});
