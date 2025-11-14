<?php

use CleaniqueCoders\AppPulse\Models\Monitor;

it('allows alert when no previous alert was sent', function () {
    $monitor = Monitor::factory()->create([
        'last_alerted_at' => null,
        'alert_throttle_minutes' => 60,
    ]);

    expect($monitor->shouldThrottleAlert())->toBeFalse();
});

it('throttles alert when within throttle window', function () {
    $monitor = Monitor::factory()->create([
        'last_alerted_at' => now()->subMinutes(30),
        'alert_throttle_minutes' => 60,
    ]);

    expect($monitor->shouldThrottleAlert())->toBeTrue();
});

it('allows alert when throttle window has passed', function () {
    $monitor = Monitor::factory()->create([
        'last_alerted_at' => now()->subMinutes(61),
        'alert_throttle_minutes' => 60,
    ]);

    expect($monitor->shouldThrottleAlert())->toBeFalse();
});

it('marks alert as sent', function () {
    $monitor = Monitor::factory()->create([
        'last_alerted_at' => null,
    ]);

    $monitor->markAlertSent();

    expect($monitor->fresh()->last_alerted_at)->not->toBeNull();
});
