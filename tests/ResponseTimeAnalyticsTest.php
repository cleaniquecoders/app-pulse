<?php

use CleaniqueCoders\AppPulse\Enums\Type;
use CleaniqueCoders\AppPulse\Models\Monitor;
use CleaniqueCoders\AppPulse\Models\MonitorHistory;

it('calculates average response time', function () {
    $monitor = Monitor::factory()->create();

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 100,
    ]);

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 200,
    ]);

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 300,
    ]);

    expect($monitor->getAverageResponseTime())->toBe(200.0);
});

it('calculates minimum response time', function () {
    $monitor = Monitor::factory()->create();

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 150,
    ]);

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 50,
    ]);

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 300,
    ]);

    expect($monitor->getMinResponseTime())->toBe(50.0);
});

it('calculates maximum response time', function () {
    $monitor = Monitor::factory()->create();

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 150,
    ]);

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 50,
    ]);

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 300,
    ]);

    expect($monitor->getMaxResponseTime())->toBe(300.0);
});

it('detects response time degradation', function () {
    $monitor = Monitor::factory()->create([
        'response_time_threshold' => 200,
    ]);

    expect($monitor->isResponseTimeDegraded(250))->toBeTrue();
    expect($monitor->isResponseTimeDegraded(150))->toBeFalse();
});

it('does not detect degradation when threshold is not set', function () {
    $monitor = Monitor::factory()->create([
        'response_time_threshold' => null,
    ]);

    expect($monitor->isResponseTimeDegraded(1000))->toBeFalse();
});

it('calculates average response time with limit', function () {
    $monitor = Monitor::factory()->create();

    // Create 5 history records
    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 100,
        'created_at' => now()->subMinutes(5),
    ]);

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 200,
        'created_at' => now()->subMinutes(4),
    ]);

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 300,
        'created_at' => now()->subMinutes(3),
    ]);

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 400,
        'created_at' => now()->subMinutes(2),
    ]);

    MonitorHistory::factory()->for($monitor)->create([
        'type' => Type::UPTIME->value,
        'response_time' => 500,
        'created_at' => now()->subMinutes(1),
    ]);

    // Get average of last 3 records (should be 300, 400, 500 = 400)
    expect($monitor->getAverageResponseTime(3))->toBe(400.0);
});
