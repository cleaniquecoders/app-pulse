# Jobs

AppPulse uses Laravel's queue system to perform monitor checks asynchronously. This prevents blocking operations and allows for efficient background processing.

## Available Jobs

### CheckMonitorJob

Performs an uptime check for a single monitor.

**Namespace:** `CleaniqueCoders\AppPulse\Jobs\CheckMonitorJob`

**Implements:** `ShouldQueue`

**Properties:**

- `Monitor $monitor` - The monitor instance to check

**Traits:**

- `Dispatchable` - Allows job dispatching
- `Queueable` - Enables queue configuration

**Process:**

1. Receives a monitor instance
2. Instantiates `CheckMonitor` action
3. Executes the uptime check
4. Action handles result storage and event dispatching

**Usage:**

```php
use CleaniqueCoders\AppPulse\Jobs\CheckMonitorJob;
use CleaniqueCoders\AppPulse\Models\Monitor;

$monitor = Monitor::first();

// Dispatch to default queue
CheckMonitorJob::dispatch($monitor);

// Dispatch to specific queue
CheckMonitorJob::dispatch($monitor)->onQueue('monitoring');

// Dispatch with delay
CheckMonitorJob::dispatch($monitor)->delay(now()->addMinutes(5));

// Dispatch with priority
CheckMonitorJob::dispatch($monitor)->onQueue('high-priority');
```

**Batch Dispatching:**

```php
use CleaniqueCoders\AppPulse\Jobs\CheckMonitorJob;
use CleaniqueCoders\AppPulse\Models\Monitor;

// Dispatch multiple monitors
$monitors = Monitor::where('status', true)->get();

foreach ($monitors as $monitor) {
    CheckMonitorJob::dispatch($monitor);
}

// Or use chunk for efficiency
Monitor::where('status', true)
    ->chunk(100, function ($monitors) {
        foreach ($monitors as $monitor) {
            CheckMonitorJob::dispatch($monitor);
        }
    });
```

---

### CheckSslJob

Performs an SSL certificate validation for a single monitor.

**Namespace:** `CleaniqueCoders\AppPulse\Jobs\CheckSslJob`

**Implements:** `ShouldQueue`

**Properties:**

- `Monitor $monitor` - The monitor instance to check

**Traits:**

- `Dispatchable` - Allows job dispatching
- `Queueable` - Enables queue configuration

**Process:**

1. Receives a monitor instance
2. Instantiates `CheckSsl` action
3. Executes the SSL validation
4. Action handles result storage and event dispatching

**Usage:**

```php
use CleaniqueCoders\AppPulse\Jobs\CheckSslJob;
use CleaniqueCoders\AppPulse\Models\Monitor;

$monitor = Monitor::first();

// Dispatch SSL check
CheckSslJob::dispatch($monitor);

// Only check monitors with SSL enabled
$monitors = Monitor::where('ssl_check', true)->get();

foreach ($monitors as $monitor) {
    CheckSslJob::dispatch($monitor);
}
```

## Queue Configuration

### Using Config

Set the default queue in `config/app-pulse.php`:

```php
'scheduler' => [
    'queue' => env('APP_PULSE_SCHEDULER_QUEUE', 'monitoring'),
],
```

### Per-Job Configuration

```php
// Dispatch to specific queue
CheckMonitorJob::dispatch($monitor)
    ->onQueue('high-priority');

// Dispatch after delay
CheckMonitorJob::dispatch($monitor)
    ->delay(now()->addMinutes(10));

// Dispatch with connection
CheckMonitorJob::dispatch($monitor)
    ->onConnection('redis');
```

### Multiple Queues

Configure different queues for different priorities:

```php
// Critical monitors - high priority queue
if ($monitor->is_critical) {
    CheckMonitorJob::dispatch($monitor)->onQueue('critical');
} else {
    CheckMonitorJob::dispatch($monitor)->onQueue('standard');
}
```

## Job Chaining

Chain jobs for sequential processing:

```php
use CleaniqueCoders\AppPulse\Jobs\CheckMonitorJob;
use CleaniqueCoders\AppPulse\Jobs\CheckSslJob;

CheckMonitorJob::withChain([
    new CheckSslJob($monitor),
])->dispatch($monitor);
```

## Job Batching

Process multiple monitors as a batch:

```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use CleaniqueCoders\AppPulse\Jobs\CheckMonitorJob;

$monitors = Monitor::where('status', true)->get();

$jobs = $monitors->map(function ($monitor) {
    return new CheckMonitorJob($monitor);
});

$batch = Bus::batch($jobs)
    ->name('Monitor Check Batch')
    ->then(function (Batch $batch) {
        // All jobs completed successfully
        logger()->info('All monitors checked successfully');
    })
    ->catch(function (Batch $batch, Throwable $e) {
        // First batch job failure detected
        logger()->error('Monitor check batch failed', ['error' => $e->getMessage()]);
    })
    ->finally(function (Batch $batch) {
        // The batch has finished executing
        logger()->info('Monitor check batch completed');
    })
    ->dispatch();
```

## Running Queue Workers

### Single Worker

```bash
php artisan queue:work
```

### Specific Queue

```bash
php artisan queue:work --queue=monitoring,default
```

### Multiple Workers

```bash
# Terminal 1
php artisan queue:work --queue=critical

# Terminal 2
php artisan queue:work --queue=monitoring

# Terminal 3
php artisan queue:work --queue=default
```

### Production Setup

Use Supervisor for process management. Example config:

```ini
[program:app-pulse-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=monitoring --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

## Job Retry Logic

Configure retry attempts in your `.env`:

```env
QUEUE_RETRY_AFTER=90
```

Or on the job:

```php
CheckMonitorJob::dispatch($monitor)
    ->onQueue('monitoring')
    ->tries(3)
    ->timeout(60)
    ->retryAfter(60);
```

## Failed Jobs

### View Failed Jobs

```bash
php artisan queue:failed
```

### Retry Failed Jobs

```bash
# Retry all failed jobs
php artisan queue:retry all

# Retry specific job
php artisan queue:retry 5
```

### Clear Failed Jobs

```bash
php artisan queue:flush
```

## Job Events

Listen to job lifecycle events:

```php
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobProcessed;

Queue::before(function (JobProcessing $event) {
    logger()->info('Job starting', ['job' => $event->job->getName()]);
});

Queue::after(function (JobProcessed $event) {
    logger()->info('Job completed', ['job' => $event->job->getName()]);
});
```

## Performance Considerations

### Chunking

Process monitors in chunks to prevent memory exhaustion:

```php
Monitor::where('status', true)
    ->chunk(100, function ($monitors) {
        foreach ($monitors as $monitor) {
            CheckMonitorJob::dispatch($monitor);
        }
    });
```

### Rate Limiting

Limit job processing rate:

```php
use Illuminate\Support\Facades\Redis;

public function handle()
{
    Redis::throttle('monitor-check')
        ->allow(10)
        ->every(60)
        ->then(function () {
            // Execute check
        }, function () {
            // Could not obtain lock, retry
            return $this->release(10);
        });
}
```

### Queue Priority

Process critical monitors first:

```bash
php artisan queue:work --queue=critical,monitoring,default
```

This ensures jobs in the `critical` queue are processed before `monitoring` and `default` queues.
