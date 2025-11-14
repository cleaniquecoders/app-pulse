<?php

namespace CleaniqueCoders\AppPulse\Models;

use CleaniqueCoders\AppPulse\Enums\Type;
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $uuid
 * @property int $owner_id
 * @property string $owner_type
 * @property string $url
 * @property string $status
 * @property int $interval
 * @property bool $ssl_check
 * @property \Carbon\Carbon|null $last_checked_at
 */
class Monitor extends Model
{
    use HasFactory;
    use InteractsWithUuid;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'owner_id',
        'owner_type',
        'url',
        'status',
        'interval',
        'timeout',
        'retry_attempts',
        'retry_delay',
        'response_time_threshold',
        'ssl_check',
        'is_maintenance',
        'maintenance_start_at',
        'maintenance_end_at',
        'alert_throttle_minutes',
        'last_alerted_at',
        'notification_channels',
        'last_checked_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
        'ssl_check' => 'boolean',
        'is_maintenance' => 'boolean',
        'maintenance_start_at' => 'datetime',
        'maintenance_end_at' => 'datetime',
        'last_alerted_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'notification_channels' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Monitor $monitor) {
            $monitor->histories()->delete();
        });
    }

    /**
     * @return MorphTo<Model, Monitor>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<MonitorHistory, Monitor>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(MonitorHistory::class);
    }

    public function hasHistory(Type $type): bool
    {
        return $this->histories()->where('type', $type->value)->exists();
    }

    public function getLatestHistory(Type $type): MonitorHistory
    {
        return $this->histories()->where('type', $type->value)->latest()->first();
    }

    /**
     * Check if monitor is currently in maintenance mode
     */
    public function isInMaintenance(): bool
    {
        if (! $this->is_maintenance) {
            return false;
        }

        $now = now();

        // If no maintenance window is set, assume ongoing maintenance
        if (! $this->maintenance_start_at && ! $this->maintenance_end_at) {
            return true;
        }

        // Check if current time is within maintenance window
        if ($this->maintenance_start_at && $this->maintenance_end_at) {
            return $now->between($this->maintenance_start_at, $this->maintenance_end_at);
        }

        // If only start time is set, check if it has started
        if ($this->maintenance_start_at && ! $this->maintenance_end_at) {
            return $now->gte($this->maintenance_start_at);
        }

        return false;
    }

    /**
     * Check if alerts should be throttled
     */
    public function shouldThrottleAlert(): bool
    {
        if (! $this->last_alerted_at) {
            return false;
        }

        $throttleUntil = $this->last_alerted_at->addMinutes($this->alert_throttle_minutes);

        return now()->lt($throttleUntil);
    }

    /**
     * Mark that an alert was sent
     */
    public function markAlertSent(): void
    {
        $this->update(['last_alerted_at' => now()]);
    }

    /**
     * Get average response time for uptime checks
     */
    public function getAverageResponseTime(?int $limit = null): float
    {
        $query = $this->histories()
            ->where('type', Type::UPTIME->value)
            ->whereNotNull('response_time');

        if ($limit) {
            $histories = $query->latest()->limit($limit)->get();

            return $histories->avg('response_time');
        }

        return (float) $query->avg('response_time');
    }

    /**
     * Get minimum response time for uptime checks
     */
    public function getMinResponseTime(?int $limit = null): float
    {
        $query = $this->histories()
            ->where('type', Type::UPTIME->value)
            ->whereNotNull('response_time');

        if ($limit) {
            $histories = $query->latest()->limit($limit)->get();

            return $histories->min('response_time');
        }

        return (float) $query->min('response_time');
    }

    /**
     * Get maximum response time for uptime checks
     */
    public function getMaxResponseTime(?int $limit = null): float
    {
        $query = $this->histories()
            ->where('type', Type::UPTIME->value)
            ->whereNotNull('response_time');

        if ($limit) {
            $histories = $query->latest()->limit($limit)->get();

            return $histories->max('response_time');
        }

        return (float) $query->max('response_time');
    }

    /**
     * Check if response time exceeds threshold
     */
    public function isResponseTimeDegraded(float $responseTime): bool
    {
        if (! $this->response_time_threshold) {
            return false;
        }

        return $responseTime > $this->response_time_threshold;
    }
}
