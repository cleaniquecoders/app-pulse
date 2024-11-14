<?php

namespace CleaniqueCoders\AppPulse\Models;

use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Model Monitor
 *
 * Represents a monitoring instance for a URL, handling details such as status, interval, SSL checks,
 * and relationships with its owner and historical records.
 *
 *
 * @property string $uuid Unique identifier for the monitor.
 * @property int $owner_id ID of the owner entity.
 * @property string $owner_type Type of the owner entity (polymorphic).
 * @property string $url URL being monitored.
 * @property string $status Current status of the monitor.
 * @property int $interval Monitoring interval in minutes.
 * @property bool $ssl_check Whether SSL checks are enabled for the monitor.
 * @property \Carbon\Carbon|null $last_checked_at Timestamp of the last check performed.
 */
class Monitor extends Model
{
    use HasFactory, InteractsWithUuid;

    /**
     * Fillable attributes for mass assignment.
     *
     * @var array<string>
     */
    protected $fillable = [
        'uuid',
        'owner_id',
        'owner_type',
        'url',
        'status',
        'interval',
        'ssl_check',
        'last_checked_at',
    ];

    /**
     * Boot method to add model event listeners.
     *
     * Deletes associated histories when a monitor is deleted.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Monitor $monitor) {
            $monitor->histories()->delete();
        });
    }

    /**
     * Get the owner of the monitor (polymorphic relationship).
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the monitoring history records associated with the monitor.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(MonitorHistory::class);
    }
}
