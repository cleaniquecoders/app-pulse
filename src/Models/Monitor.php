<?php

namespace CleaniqueCoders\AppPulse\Models;

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
        'ssl_check',
        'last_checked_at',
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
}
