# Multi-Channel Notifications

AppPulse v1.2.0 introduces a comprehensive multi-channel notification system that allows you to receive alerts through various platforms.

## Supported Channels

- **Slack** - Receive notifications in Slack channels
- **Discord** - Get alerts in Discord servers
- **Microsoft Teams** - Send notifications to Teams channels
- **Custom Webhooks** - Integrate with any webhook-compatible service

## Configuration

### Global Configuration

Configure notification channels in your `.env` file:

```env
# Enable/disable all notifications
APP_PULSE_NOTIFICATIONS_ENABLED=true

# Slack
APP_PULSE_SLACK_ENABLED=true
APP_PULSE_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL

# Discord
APP_PULSE_DISCORD_ENABLED=true
APP_PULSE_DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/YOUR/WEBHOOK/URL

# Microsoft Teams
APP_PULSE_TEAMS_ENABLED=true
APP_PULSE_TEAMS_WEBHOOK_URL=https://outlook.office.com/webhook/YOUR/WEBHOOK/URL

# Custom Webhook
APP_PULSE_WEBHOOK_ENABLED=true
APP_PULSE_WEBHOOK_URL=https://your-domain.com/webhook/endpoint
```

### Per-Monitor Configuration

You can also configure notification channels per monitor:

```php
$monitor->update([
    'notification_channels' => ['slack', 'discord'],
]);
```

## Setting Up Slack

1. Go to your Slack workspace
2. Navigate to **Apps** → **Incoming Webhooks**
3. Click **Add to Slack**
4. Select a channel and authorize the app
5. Copy the webhook URL to your `.env` file

Example notification in Slack:

```
Monitor UP: https://example.com
Monitor `https://example.com` is now **up**
AppPulse • Just now
```

## Setting Up Discord

1. Open your Discord server
2. Go to **Server Settings** → **Integrations**
3. Click **Create Webhook**
4. Configure the webhook and copy the URL
5. Add the URL to your `.env` file

Example notification in Discord:

```
Monitor DOWN: https://example.com
Monitor `https://example.com` is now **down**
AppPulse • 2023-11-15 10:30:00
```

## Setting Up Microsoft Teams

1. Open your Teams channel
2. Click **···** (More options)
3. Select **Connectors**
4. Find **Incoming Webhook** and configure
5. Copy the webhook URL to your `.env` file

Example notification in Teams:

```
AppPulse
2023-11-15 10:30:00
Monitor `https://example.com` is now **down**
```

## Custom Webhooks

For custom webhooks, AppPulse sends a JSON payload in this format:

```json
{
  "event": "monitor_alert",
  "title": "Monitor UP: https://example.com",
  "message": "Monitor `https://example.com` is now **up**",
  "severity": "success",
  "timestamp": "2023-11-15T10:30:00.000000Z",
  "source": "AppPulse"
}
```

### Severity Levels

- `success` - Monitor is up or SSL is valid
- `danger` - Monitor is down or SSL is expired
- `warning` - Performance degradation or SSL expiring soon
- `info` - General information

### Example Custom Handler

```php
// routes/web.php
Route::post('/webhook/app-pulse', function (Request $request) {
    $data = $request->all();

    // Log the alert
    logger()->info('AppPulse Alert', $data);

    // Custom logic based on severity
    if ($data['severity'] === 'danger') {
        // Send SMS, page someone, etc.
    }

    return response()->json(['success' => true]);
});
```

## Event Types

AppPulse sends notifications for these events:

### 1. Uptime Changes

Triggered when a monitor's status changes (up ↔ down):

```
Monitor DOWN: https://example.com
Monitor `https://example.com` is now **down**
```

### 2. SSL Status Changes

Triggered when SSL certificate status changes:

```
SSL Status Changed: https://example.com
SSL certificate for `https://example.com` is now **expired**
```

### 3. Performance Degradation

Triggered when response time exceeds threshold:

```
Performance Degraded: https://example.com
Monitor `https://example.com` response time (**523.45ms**) exceeded threshold (**500.00ms**)
```

## Creating Custom Notification Channels

You can create your own notification channels by implementing the `NotificationChannel` contract:

```php
<?php

namespace App\Notifications\Channels;

use CleaniqueCoders\AppPulse\Contracts\NotificationChannel;
use CleaniqueCoders\AppPulse\Models\Monitor;

class TelegramChannel implements NotificationChannel
{
    public function sendUptimeNotification(Monitor $monitor, string $status): void
    {
        // Send notification to Telegram
    }

    public function sendSslNotification(Monitor $monitor, string $status): void
    {
        // Send SSL notification to Telegram
    }

    public function sendPerformanceDegradedNotification(
        Monitor $monitor,
        float $responseTime,
        float $threshold
    ): void {
        // Send performance alert to Telegram
    }

    public function isEnabled(): bool
    {
        return config('services.telegram.enabled', false);
    }
}
```

### Register Custom Channel

```php
// In a service provider
use CleaniqueCoders\AppPulse\Notifications\NotificationManager;
use App\Notifications\Channels\TelegramChannel;

public function boot()
{
    $manager = app(NotificationManager::class);
    $manager->addChannel(new TelegramChannel);
}
```

## Custom Event Listeners

If you need more control, you can create custom event listeners:

```php
<?php

namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use Illuminate\Support\Facades\Mail;

class SendEmailOnMonitorDown
{
    public function handle(MonitorUptimeChanged $event): void
    {
        if ($event->status->value === 'down') {
            Mail::to('admin@example.com')->send(
                new MonitorDownNotification($event->monitor)
            );
        }
    }
}
```

Register in `EventServiceProvider`:

```php
protected $listen = [
    MonitorUptimeChanged::class => [
        SendEmailOnMonitorDown::class,
    ],
];
```

## Notification Throttling

To prevent notification spam, use alert throttling:

```php
$monitor->update([
    'alert_throttle_minutes' => 120, // Only alert every 2 hours
]);
```

This ensures that even if a monitor's status changes multiple times, you'll only receive one notification per throttle window.

## Testing Notifications

### Test Slack Notification

```bash
curl -X POST YOUR_SLACK_WEBHOOK_URL \
  -H 'Content-Type: application/json' \
  -d '{
    "attachments": [{
      "color": "danger",
      "title": "Test Alert",
      "text": "This is a test notification from AppPulse"
    }]
  }'
```

### Test Discord Notification

```bash
curl -X POST YOUR_DISCORD_WEBHOOK_URL \
  -H 'Content-Type: application/json' \
  -d '{
    "embeds": [{
      "title": "Test Alert",
      "description": "This is a test notification from AppPulse",
      "color": 15158332
    }]
  }'
```

## Troubleshooting

### Notifications Not Sending

1. Check that notifications are enabled:
   ```php
   config('app-pulse.notifications.enabled'); // Should be true
   ```

2. Verify webhook URLs are correct
3. Check Laravel logs for errors:
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. Ensure the queue is running if listeners use `ShouldQueue`:
   ```bash
   php artisan queue:work
   ```

### Testing Individual Channels

```php
use CleaniqueCoders\AppPulse\Notifications\NotificationManager;

$manager = app(NotificationManager::class);
$monitor = Monitor::first();

// Test uptime notification
$manager->sendUptimeNotification($monitor, 'down');

// Test performance notification
$manager->sendPerformanceDegradedNotification($monitor, 523.45, 500.00);
```

## Best Practices

1. **Use Alert Throttling**: Prevent notification fatigue by setting appropriate throttle windows
2. **Configure Maintenance Mode**: Suppress alerts during planned maintenance
3. **Choose Appropriate Channels**: Different channels for different severity levels
4. **Test Webhooks**: Always test webhook URLs before deploying
5. **Monitor Logs**: Keep an eye on notification delivery failures
6. **Queue Notifications**: Use queued listeners for better performance

## Next Steps

- Learn about [Alert Management](07-alert-management.md)
- Configure [Maintenance Mode](06-maintenance-mode.md)
- Set up [Response Time Tracking](03-response-time-tracking.md)
