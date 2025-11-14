<?php

namespace CleaniqueCoders\AppPulse\Models;

use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitorHistory extends Model
{
    use HasFactory, InteractsWithUuid;

    protected $fillable = [
        'uuid',
        'monitor_id',
        'type',
        'status',
        'response_time',
        'error_message',
        'retry_count',
    ];

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }
}
