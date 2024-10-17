<?php

namespace CleaniqueCoders\AppPulse\Models;

use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function monitor()
    {
        return $this->belongsTo(Monitor::class);
    }
}
