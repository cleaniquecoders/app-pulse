<?php

namespace CleaniqueCoders\AppPulse\Models;

use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monitor extends Model
{
    use HasFactory, InteractsWithUuid;

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

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Monitor $monitor) {
            $monitor->histories()->delete();
        });
    }

    public function owner()
    {
        return $this->morphTo(); // Polymorphic relationship
    }

    public function histories()
    {
        return $this->hasMany(MonitorHistory::class);
    }
}
