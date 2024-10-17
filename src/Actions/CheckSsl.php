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
    public function __construct(
        protected Monitor $monitor,
        protected bool $force = false
    ) {}

    public function execute(): self
    {
        if (! $this->monitor->ssl_check && ! $this->force) {
            return $this;
        }

        try {
            $host = $this->getHost($this->monitor->url);
            $certificate = SslCertificate::createForHost($host);

            $daysUntilExpiration = $certificate->expirationDate()->diffInDays();
            $status = $certificate->isExpired() ? SslStatus::EXPIRED : SslStatus::VALID;

            $this->createCheckHistory($status, $daysUntilExpiration);
        } catch (Exception $e) {
            $this->createCheckHistory(SslStatus::FAILED_CHECK, 0, $e->getMessage());
        }

        return $this;
    }

    private function getHost(string $url): string
    {
        return parse_url($url, PHP_URL_HOST) ?? $url;
    }

    private function createCheckHistory(SslStatus $status, int $response_time, ?string $error_message = null)
    {
        MonitorHistory::create([
            'uuid' => Str::orderedUuid(),
            'monitor_id' => $this->monitor->id,
            'type' => Type::SSL->value,
            'status' => $status->value,
            'response_time' => $response_time,
            'error_message' => $error_message,
        ]);

        if ($status->value != $this->monitor->status) {
            $this->monitor->update([
                'status' => $status->value,
            ]);

            SslStatusChanged::dispatch($this->monitor, $status);
        }
    }
}
