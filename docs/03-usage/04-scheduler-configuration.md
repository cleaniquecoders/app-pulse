# Scheduler Configuration

Configure automated monitor checks using Laravel's scheduler.

## Basic Setup

In `app/Console/Kernel.php`:

```php
use Illuminate\Console\Scheduling\Schedule;

protected function schedule(Schedule $schedule)
{
    $interval = config('app-pulse.scheduler.interval', 10);

    $schedule->command('monitor:check-status')
        ->everyMinutes($interval);
}
```

## Custom Schedules

### Different Intervals for Different Times

```php
// More frequent checks during business hours
$schedule->command('monitor:check-status --chunk-size=50')
    ->weekdays()
    ->between('8:00', '18:00')
    ->everyFiveMinutes();

// Less frequent at night
$schedule->command('monitor:check-status')
    ->weekdays()
    ->between('18:00', '8:00')
    ->everyFifteenMinutes();

// Even less frequent on weekends
$schedule->command('monitor:check-status')
    ->weekends()
    ->everyThirtyMinutes();
```

### Priority Queues

```php
// Critical monitors - check frequently
$schedule->command('monitor:check-status --queue=critical')
    ->everyMinute();

// Standard monitors - normal interval
$schedule->command('monitor:check-status --queue=standard')
    ->everyTenMinutes();
```

## Configuration Options

### Environment Variables

```env
APP_PULSE_SCHEDULER_INTERVAL=10
APP_PULSE_SCHEDULER_QUEUE=monitoring
APP_PULSE_SCHEDULER_CHUNK=100
```

### Config File

In `config/app-pulse.php`:

```php
return [
    'scheduler' => [
        'interval' => env('APP_PULSE_SCHEDULER_INTERVAL', 10),
        'queue' => env('APP_PULSE_SCHEDULER_QUEUE', 'default'),
        'chunk' => env('APP_PULSE_SCHEDULER_CHUNK', 100),
    ],
];
```

## Server Setup

### Cron Entry

Add to your server's crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Verify Cron

```bash
# Check if cron is running
ps aux | grep cron

# View crontab
crontab -l

# Edit crontab
crontab -e
```

## Queue Workers

### Single Worker

```bash
php artisan queue:work --queue=monitoring
```

### Supervisor Configuration

Create `/etc/supervisor/conf.d/app-pulse.conf`:

```ini
[program:app-pulse-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --queue=monitoring --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
```

Reload Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start app-pulse-worker:*
```

## Monitoring Scheduler

### List Scheduled Tasks

```bash
php artisan schedule:list
```

### Test Schedule

```bash
# Run scheduler manually
php artisan schedule:run

# View schedule without running
php artisan schedule:test
```

## Advanced Patterns

### Conditional Scheduling

```php
$schedule->command('monitor:check-status')
    ->when(function () {
        return config('app.env') === 'production';
    })
    ->everyTenMinutes();
```

### Callback After Check

```php
$schedule->command('monitor:check-status')
    ->everyTenMinutes()
    ->onSuccess(function () {
        Log::info('Monitor checks completed successfully');
    })
    ->onFailure(function () {
        Log::error('Monitor check schedule failed');
    });
```

### Prevent Overlaps

```php
$schedule->command('monitor:check-status')
    ->everyTenMinutes()
    ->withoutOverlapping();
```
