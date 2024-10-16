<?php

use CleaniqueCoders\AppPulse\Models\Monitor;
use CleaniqueCoders\AppPulse\Models\MonitorHistory;

it('creates a monitor with valid attributes', function () {
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com',
        'status' => 'pending',
        'interval' => 10,
        'ssl_check' => true,
    ]);

    expect($monitor->uuid)->not->toBeNull();
    expect($monitor->url)->toBe('https://example.com');
    expect($monitor->status)->toBe('pending');
    expect($monitor->interval)->toBe(10);
    expect($monitor->ssl_check)->toBeTrue();
});

it('a monitor can have many histories', function () {
    $monitor = Monitor::factory()->create();
    MonitorHistory::factory()->count(3)->for($monitor)->create();

    expect($monitor->histories)->toHaveCount(3);
});

it('creates a monitor history record with valid attributes', function () {
    $history = MonitorHistory::factory()->create([
        'type' => 'uptime',
        'status' => 'up',
        'response_time' => 200,
        'error_message' => null,
    ]);

    expect($history->uuid)->not->toBeNull();
    expect($history->type)->toBe('uptime');
    expect($history->status)->toBe('up');
    expect($history->response_time)->toBe(200);
    expect($history->error_message)->toBeNull();
});

it('retrieves histories for a monitor', function () {
    $monitor = Monitor::factory()->create();
    MonitorHistory::factory()->count(2)->for($monitor)->create();

    $histories = $monitor->histories;

    expect($histories)->toHaveCount(2);
    expect($histories->first()->monitor_id)->toBe($monitor->id);
});

it('deletes histories when a monitor is deleted', function () {
    $monitor = Monitor::factory()->create();
    MonitorHistory::factory()->count(3)->for($monitor)->create();

    // Assert histories exist before deletion
    expect(MonitorHistory::where('monitor_id', $monitor->id)->count())->toBe(3);

    // Delete the monitor
    $monitor->delete();

    // Assert that histories are deleted
    expect(MonitorHistory::where('monitor_id', $monitor->id)->count())->toBe(0);
});
