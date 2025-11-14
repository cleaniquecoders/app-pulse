# Configuration

After installing AppPulse, you need to publish its configuration and migration files.

## Publish Assets

### Publish Configuration File

```bash
php artisan vendor:publish --tag="app-pulse-config"
```

This creates the configuration file at `config/app-pulse.php`.

### Publish Migrations

```bash
php artisan vendor:publish --tag="app-pulse-migrations"
```

This publishes the database migrations to your `database/migrations` directory.

## Run Migrations

Execute the migrations to create the necessary database tables:

```bash
php artisan migrate
```

This creates the following tables:

- `monitors` - Stores monitor configurations
- `monitor_histories` - Stores monitoring check results

## Configuration Options

The `config/app-pulse.php` file contains the following options:

```php
return [
    'events' => [
        \CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged::class => [],
        \CleaniqueCoders\AppPulse\Events\SslStatusChanged::class => [],
    ],

    'scheduler' => [
        'interval' => env('APP_PULSE_SCHEDULER_INTERVAL', 1),
        'queue' => env('APP_PULSE_SCHEDULER_QUEUE', 'default'),
        'chunk' => env('APP_PULSE_SCHEDULER_CHUNK', 100),
    ],
];
```

### Events

The `events` array allows you to register event listeners for monitoring events. By default, it includes:

- `MonitorUptimeChanged` - Fired when a monitor's uptime status changes
- `SslStatusChanged` - Fired when SSL certificate status changes

### Scheduler Configuration

#### interval

- **Type**: Integer (minutes)
- **Default**: `1`
- **Environment Variable**: `APP_PULSE_SCHEDULER_INTERVAL`
- **Description**: Time interval (in minutes) between automated monitor checks

#### queue

- **Type**: String
- **Default**: `default`
- **Environment Variable**: `APP_PULSE_SCHEDULER_QUEUE`
- **Description**: Queue name to use for background monitor checks

#### chunk

- **Type**: Integer
- **Default**: `100`
- **Environment Variable**: `APP_PULSE_SCHEDULER_CHUNK`
- **Description**: Number of monitors to process per batch

## Environment Variables

Add these to your `.env` file to customize the configuration:

```env
# Check monitors every 10 minutes
APP_PULSE_SCHEDULER_INTERVAL=10

# Use a dedicated monitoring queue
APP_PULSE_SCHEDULER_QUEUE=monitoring

# Process 50 monitors per batch
APP_PULSE_SCHEDULER_CHUNK=50
```

## Set Up Laravel Scheduler

AppPulse uses Laravel's scheduler for automated checks. Ensure the scheduler is running by adding this cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path-to-your-project` with the actual path to your Laravel application.

## Verify Configuration

After configuration, verify everything is set up correctly:

```bash
# Check if migrations ran successfully
php artisan migrate:status

# Test the scheduler (optional)
php artisan schedule:list
```

## Next Steps

Now that AppPulse is configured, proceed to [Quick Start](04-quick-start.md) to create your first monitor.
