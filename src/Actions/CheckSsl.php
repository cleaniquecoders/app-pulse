<?php

namespace CleaniqueCoders\AppPulse\Actions;

use CleaniqueCoders\AppPulse\Enums\SslStatus;
use CleaniqueCoders\AppPulse\Enums\Type;
use CleaniqueCoders\AppPulse\Events\SslStatusChanged;
use CleaniqueCoders\AppPulse\Models\Monitor;
use CleaniqueCoders\Traitify\Contracts\Execute;
use Exception;
use Illuminate\Support\Str;
use Spatie\SslCertificate\SslCertificate;

class CheckSsl implements Execute
{
    protected ?SslCertificate $mockCertificate = null;

    public function __construct(
        protected Monitor $monitor,
        protected bool $force = false
    ) {}

    public function mock(SslCertificate $certificate): self
    {
        $this->mockCertificate = $certificate;

        return $this;
    }

    public function execute(): self
    {
        if (! $this->monitor->ssl_check && ! $this->force) {
            return $this;
        }

        if (! $this->monitor->url) {
            return $this;
        }

        try {
            $host = $this->getHost($this->monitor->url);

            if (! is_string($host)) {
                throw new Exception('Invalid URL given');
            }

            $certificate = $this->mockCertificate ?? SslCertificate::createForHostName($host);

            if ($certificate instanceof SslCertificate) {
                $daysUntilExpiration = $certificate->expirationDate()->diffInDays();
                $status = $certificate->isExpired() ? SslStatus::EXPIRED : SslStatus::VALID;

                $this->createCheckHistory($status, $daysUntilExpiration);
            } else {
                throw new Exception('Unable to Check SSL');
            }
        } catch (Exception $e) {
            $this->createCheckHistory(SslStatus::FAILED_CHECK, 0, $e->getMessage());
        }

        return $this;
    }

    private function getHost(string $url): string|bool
    {
        return parse_url($url, PHP_URL_HOST) ?? $url;
    }

    private function createCheckHistory(SslStatus $status, int|float $response_time, ?string $error_message = null): void
    {
        MonitorHistory::create([
            'uuid' => Str::orderedUuid(),
            'monitor_id' => $this->monitor->id,
            'type' => Type::SSL->value,
            'status' => $status->value,
            'response_time' => $response_time,
            'error_message' => $error_message,
        ]);

        if ($this->monitor->hasHistory(Type::SSL)) {
            $previous_history = $this->monitor->getLatestHistory(Type::SSL);
            $previous_status = SslStatus::tryFrom($previous_history->status);

            if ($previous_status && $status->value != $previous_status?->value) {
                SslStatusChanged::dispatch($this->monitor, $status);
            }

            return;
        }

        SslStatusChanged::dispatch($this->monitor, $status);
    }
}
