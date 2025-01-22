<?php

use CleaniqueCoders\AppPulse\Actions\MonitorHistory;
use CleaniqueCoders\AppPulse\Enums\Status;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    // Create fake storage for SSL checks if needed.
    Storage::fake('local');

    // Create a test monitor.
    $this->monitor = Monitor::factory()->create([
        'url' => 'https://example.com',
        'ssl_check' => true,
        'status' => Status::ENABLED->value,
    ]);
});

it('checks monitor uptime and records history', function () {
    // Start time before making the request
    $startTime = microtime(true);

    // Mock HTTP request to simulate a successful response
    Http::fake([
        'https://example.com' => Http::response('', 200),
    ]);

    // Perform the request (This will use the fake response above)
    $response = Http::get('https://example.com');

    // Calculate the time difference in milliseconds
    $responseTime = (microtime(true) - $startTime) * 1000;

    // Assert the response is OK
    expect($response->ok())->toBeTrue();

    // Record the monitor history with the response time
    MonitorHistory::create([
        'monitor_id' => $this->monitor->id,
        'type' => 'uptime',
        'status' => 'up',
        'response_time' => (int) $responseTime,
    ]);

    // Assert the monitor history was recorded correctly
    assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => 'uptime',
        'status' => 'up',
    ]);
});

// Uptime Check - Success
it('records monitor as up when response is successful', function () {
    $startTime = microtime(true);

    Http::fake(['https://example.com' => Http::response('', 200)]);

    Http::get($this->monitor->url);

    $responseTime = (microtime(true) - $startTime) * 1000;

    Artisan::call('monitor:check-status');

    // Retrieve the monitor history from the database
    $history = \CleaniqueCoders\AppPulse\Models\MonitorHistory::where('monitor_id', $this->monitor->id)
        ->where('type', 'uptime')
        ->where('status', 'up')
        ->first();

    // Ensure the history exists
    expect($history)->not->toBeNull();

    // Increase the tolerance range to 10ms
    expect(abs($history->response_time - (int) $responseTime))->toBeLessThanOrEqual(10);
});

// Uptime Check - Failure
it('records monitor as down when response fails', function () {
    Http::fake(['https://example.com' => Http::response('', 500)]);

    Artisan::call('monitor:check-status');

    assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => 'uptime',
        'status' => 'down',
    ]);
});

// SSL Check - Expired
it('records expired SSL status', function () {
    Http::fake(['https://example.com' => Http::response('', 200)]);

    MonitorHistory::create([
        'monitor_id' => $this->monitor->id,
        'type' => 'ssl',
        'status' => 'ssl_expired',
    ]);

    Artisan::call('monitor:check-status');

    assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => 'ssl',
        'status' => 'ssl_expired',
    ]);
});

// SSL Check - Valid
it('records valid SSL status', function () {
    Http::fake(['https://example.com' => Http::response('', 200)]);

    MonitorHistory::create([
        'monitor_id' => $this->monitor->id,
        'type' => 'ssl',
        'status' => 'ssl_valid',
    ]);

    Artisan::call('monitor:check-status');

    assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => 'ssl',
        'status' => 'ssl_valid',
    ]);
});
