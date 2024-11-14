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
    Mockery::close();
});

// Test: Valid SSL Certificate
it('creates valid SSL history when the certificate is valid', function () {
    $sslMock = Mockery::mock(SslCertificate::class);
    $sslMock->shouldReceive('createForHostName')
        ->with('example.com')
        ->andReturnSelf();
    $sslMock->shouldReceive('expirationDate')
        ->andReturn(now()->addDays(30));
    $sslMock->shouldReceive('isExpired')
        ->andReturn(false);

    (new CheckSsl($this->monitor))->mock($sslMock)->execute();

    $this->assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => Type::SSL->value,
        'status' => SslStatus::VALID->value,
    ]);
});

// Test: Expired SSL Certificate
it('creates expired SSL history if the certificate has expired', function () {
    $sslMock = Mockery::mock(SslCertificate::class);
    $sslMock->shouldReceive('createForHostName')
        ->with('example.com')
        ->andReturnSelf();
    $sslMock->shouldReceive('expirationDate')
        ->andReturn(now()->subDays(1));
    $sslMock->shouldReceive('isExpired')
        ->andReturn(true);

    (new CheckSsl($this->monitor))->mock($sslMock)->execute();

    $this->assertDatabaseHas('monitor_histories', [
        'monitor_id' => $this->monitor->id,
        'type' => Type::SSL->value,
        'status' => SslStatus::EXPIRED->value,
    ]);
});
