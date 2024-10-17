<?php

use CleaniqueCoders\AppPulse\Actions\CheckMonitor;
use CleaniqueCoders\AppPulse\Enums\SiteStatus;
use CleaniqueCoders\AppPulse\Enums\Type;
use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Set up a test monitor
    $this->monitor = Monitor::factory()->create([
        'url' => 'https://example.com',
        'status' => SiteStatus::DOWN->value,
    ]);

    Event::fake();  // Prevent actual event dispatching
});

it('marks the monitor as up when the response is successful', function () {
    Http::fake(['https://example.com' => Http::response('', 200)]);

    // Execute the CheckMonitor action
    (new CheckMonitor($this->monitor))->execute();

    // Verify the monitor history is recorded as up
    $this->assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => Type::UPTIME->value,
        'status' => SiteStatus::UP->value,
    ]);

    // Verify that the monitor status was updated
    $this->assertEquals(SiteStatus::UP->value, $this->monitor->fresh()->status);

    // Ensure the event was dispatched
    Event::assertDispatched(MonitorUptimeChanged::class);
});

it('marks the monitor as down when the response fails', function () {
    Http::fake(['https://example.com' => Http::response('', 500)]);

    // Execute the CheckMonitor action
    (new CheckMonitor($this->monitor))->execute();

    // Verify the monitor history is recorded as down
    $this->assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => Type::UPTIME->value,
        'status' => SiteStatus::DOWN->value,
    ]);

    // Ensure the monitor status remains as down
    $this->assertEquals(SiteStatus::DOWN->value, $this->monitor->fresh()->status);

    // Ensure no event was dispatched since the status didn't change
    Event::assertNotDispatched(MonitorUptimeChanged::class);
});

it('logs an error message if the request fails', function () {
    Http::fake([
        'https://example.com' => function () {
            throw new \Exception('Network error');
        },
    ]);

    // Execute the CheckMonitor action
    (new CheckMonitor($this->monitor))->execute();

    // Verify the error message is logged in the monitor history
    $this->assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => Type::UPTIME->value,
        'status' => SiteStatus::DOWN->value,
        'error_message' => 'Network error',
    ]);
});
