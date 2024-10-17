<?php

use CleaniqueCoders\AppPulse\Actions\CheckSsl;
use CleaniqueCoders\AppPulse\Enums\SslStatus;
use CleaniqueCoders\AppPulse\Enums\Type;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\SslCertificate\SslCertificate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->monitor = Monitor::factory()->create([
        'url' => 'https://example.com',
        'ssl_check' => true,
    ]);
});

afterEach(function () {
    // Close Mockery to avoid conflicts between tests
    Mockery::close();
});

// Test: Valid SSL Certificate
it('creates valid SSL history when the certificate is valid', function () {
    $mock = Mockery::mock('overload:'.SslCertificate::class);
    $mock->shouldReceive('createForHost')
        ->with('example.com')
        ->andReturnSelf();
    $mock->shouldReceive('expirationDate')
        ->andReturn(now()->addDays(30));
    $mock->shouldReceive('isExpired')
        ->andReturn(false);

    (new CheckSsl($this->monitor))->execute();

    $this->assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => Type::SSL->value,
        'status' => SslStatus::VALID->value,
    ]);
});

// Test: Expired SSL Certificate
it('creates expired SSL history if the certificate has expired', function () {
    $mock = Mockery::mock('overload:'.SslCertificate::class);
    $mock->shouldReceive('createForHost')
        ->with('example.com')
        ->andReturnSelf();
    $mock->shouldReceive('expirationDate')
        ->andReturn(now()->subDays(1));
    $mock->shouldReceive('isExpired')
        ->andReturn(true);

    (new CheckSsl($this->monitor))->execute();

    $this->assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => Type::SSL->value,
        'status' => SslStatus::EXPIRED->value,
    ]);
});

// Test: Failed SSL Check
it('creates failed SSL check history if connection fails', function () {
    $mock = Mockery::mock('overload:'.SslCertificate::class);
    $mock->shouldReceive('createForHost')
        ->with('example.com')
        ->andThrow(new Exception('Connection failed'));

    (new CheckSsl($this->monitor))->execute();

    $this->assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => Type::SSL->value,
        'status' => SslStatus::FAILED_CHECK->value,
        'error_message' => 'Connection failed',
    ]);
});
