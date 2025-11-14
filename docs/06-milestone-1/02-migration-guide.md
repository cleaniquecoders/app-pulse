# Migration Guide: v1.1.0 to v1.2.0

This guide will help you upgrade from AppPulse v1.1.0 to v1.2.0.

## Requirements

- PHP 8.2, 8.3, or 8.4
- Laravel 10.x, 11.x, or 12.x

## Step 1: Update the Package

```bash
composer update cleaniquecoders/app-pulse
```

## Step 2: Publish and Run New Migrations

```bash
php artisan vendor:publish --tag="app-pulse-migrations"
php artisan migrate
```

This will run the `add_enhanced_monitoring_and_alerting_features_to_monitors_table` migration, which adds the following fields to your `monitors` table:
- `timeout` - Custom timeout per monitor (default: 10 seconds)
- `retry_attempts` - Number of retry attempts (default: 3)
- `retry_delay` - Initial retry delay in seconds (default: 1)
- `response_time_threshold` - Performance degradation threshold in milliseconds
- `is_maintenance` - Maintenance mode flag
- `maintenance_start_at` - Maintenance window start time
- `maintenance_end_at` - Maintenance window end time
- `alert_throttle_minutes` - Minimum minutes between alerts (default: 60)
- `last_alerted_at` - Timestamp of last alert sent
- `notification_channels` - JSON array of enabled notification channels

And to your `monitor_histories` table:
- `retry_count` - Number of retries performed for this check

## Step 3: Publish Updated Configuration

```bash
php artisan vendor:publish --tag="app-pulse-config" --force
```

## Step 4: Configure Environment Variables (Optional)

Add these optional environment variables to your `.env` file:

```env
# Default settings
APP_PULSE_DEFAULT_TIMEOUT=10
APP_PULSE_DEFAULT_RETRY_ATTEMPTS=3
APP_PULSE_DEFAULT_RETRY_DELAY=1
APP_PULSE_DEFAULT_ALERT_THROTTLE=60

# Notification channels
APP_PULSE_NOTIFICATIONS_ENABLED=true

# Slack
APP_PULSE_SLACK_ENABLED=true
APP_PULSE_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL

# Discord
APP_PULSE_DISCORD_ENABLED=false
APP_PULSE_DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/YOUR/WEBHOOK/URL

# Microsoft Teams
APP_PULSE_TEAMS_ENABLED=false
APP_PULSE_TEAMS_WEBHOOK_URL=https://outlook.office.com/webhook/YOUR/WEBHOOK/URL

# Custom Webhook
APP_PULSE_WEBHOOK_ENABLED=false
APP_PULSE_WEBHOOK_URL=https://your-domain.com/webhook/endpoint
```

## Step 5: Update Your Event Listeners (If Custom)

If you've created custom event listeners, note that the following events now include alert throttling by default:

- `MonitorUptimeChanged`
- `SslStatusChanged`
- `MonitorResponseTimeDegraded` (new event)

The built-in listeners will automatically respect the `alert_throttle_minutes` setting.

## Breaking Changes

### None

v1.2.0 is fully backward compatible with v1.1.0. All new fields have sensible defaults, and existing functionality remains unchanged.

## New Features You Can Start Using

### 1. Set Response Time Thresholds

```php
$monitor->update([
    'response_time_threshold' => 500, // Alert if response time > 500ms
]);
```

### 2. Enable Maintenance Mode

```php
// Ongoing maintenance (no end time)
$monitor->update([
    'is_maintenance' => true,
]);

// Scheduled maintenance window
$monitor->update([
    'is_maintenance' => true,
    'maintenance_start_at' => now()->addHours(2),
    'maintenance_end_at' => now()->addHours(4),
]);
```

### 3. Configure Custom Timeouts and Retries

```php
$monitor->update([
    'timeout' => 30, // 30 seconds
    'retry_attempts' => 5,
    'retry_delay' => 2, // 2 seconds initial delay
]);
```

### 4. Adjust Alert Throttling

```php
$monitor->update([
    'alert_throttle_minutes' => 120, // Only alert every 2 hours
]);
```

### 5. Use Response Time Analytics

```php
// Get average response time (all records)
$avgResponseTime = $monitor->getAverageResponseTime();

// Get average of last 100 checks
$recentAvg = $monitor->getAverageResponseTime(100);

// Get min/max response times
$minTime = $monitor->getMinResponseTime();
$maxTime = $monitor->getMaxResponseTime();

// Check if response time is degraded
if ($monitor->isResponseTimeDegraded($responseTime)) {
    // Handle performance degradation
}
```

### 6. Check Maintenance Status

```php
if ($monitor->isInMaintenance()) {
    // Skip certain operations
}
```

### 7. Listen to New Events

```php
// In EventServiceProvider
use CleaniqueCoders\AppPulse\Events\MonitorResponseTimeDegraded;
use App\Listeners\HandlePerformanceDegradation;

protected $listen = [
    MonitorResponseTimeDegraded::class => [
        HandlePerformanceDegradation::class,
    ],
];
```

## Testing

After migration, verify that:

1. Existing monitors continue to function normally
2. New monitors are created with default values
3. Notifications are being sent (if configured)
4. Maintenance mode works as expected

```bash
# Run a manual check
php artisan monitor:check-status

# Check the logs for any errors
tail -f storage/logs/laravel.log
```

## Rollback

If you need to rollback, run:

```bash
composer require cleaniquecoders/app-pulse:^1.1
php artisan migrate:rollback
```

## Support

If you encounter any issues during migration:

1. Check the [Troubleshooting Guide](../03-usage/06-troubleshooting.md)
2. Review the [GitHub Issues](https://github.com/cleaniquecoders/app-pulse/issues)
3. Submit a new issue with details about your environment and the problem
