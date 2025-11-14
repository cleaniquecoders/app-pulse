# Quick Start

This guide will help you create your first monitor and verify it's working correctly.

## Creating Your First Monitor

Use the `Monitor` model to create a new monitor:

```php
use CleaniqueCoders\AppPulse\Models\Monitor;

$monitor = Monitor::create([
    'owner_type' => \App\Models\User::class, // The model type that owns this monitor
    'owner_id' => 1,                         // The ID of the owner (e.g., User ID)
    'url' => 'https://example.com',          // URL to monitor
    'interval' => 10,                        // Check every 10 minutes
    'ssl_check' => true,                     // Enable SSL certificate checking
]);
```

### Monitor Properties

| Property | Type | Description |
|----------|------|-------------|
| `owner_type` | string | The fully qualified class name of the owner model |
| `owner_id` | integer | The ID of the owner model instance |
| `url` | string | The URL to monitor (must include protocol) |
| `interval` | integer | Check interval in minutes |
| `ssl_check` | boolean | Whether to check SSL certificates |

## Running Your First Check

### Manual Check

Trigger a manual check for all monitors:

```bash
php artisan monitor:check-status
```

This command will:

1. Fetch all active monitors
2. Check each URL for uptime
3. Check SSL certificates (if enabled)
4. Store results in the database
5. Dispatch relevant events

### Automated Checks

Once the Laravel scheduler is running, monitors will be checked automatically based on their configured interval.

## Viewing Results

### Check Monitor History

```php
use CleaniqueCoders\AppPulse\Models\Monitor;
use CleaniqueCoders\AppPulse\Enums\Type;

$monitor = Monitor::first();

// Check if monitor has uptime history
if ($monitor->hasHistory(Type::UPTIME)) {
    // Get latest uptime check
    $latestCheck = $monitor->getLatestHistory(Type::UPTIME);

    echo "Status: " . $latestCheck->status;
    echo "Response Time: " . $latestCheck->response_time . "ms";
}

// Check SSL history
if ($monitor->hasHistory(Type::SSL)) {
    $sslCheck = $monitor->getLatestHistory(Type::SSL);

    echo "SSL Status: " . $sslCheck->status;
    echo "Valid Until: " . $sslCheck->valid_until;
}
```

### Access All Histories

```php
$monitor = Monitor::first();

// Get all monitoring histories
$histories = $monitor->histories()->latest()->get();

foreach ($histories as $history) {
    echo "Type: " . $history->type . "\n";
    echo "Status: " . $history->status . "\n";
    echo "Checked at: " . $history->created_at . "\n";
}
```

## Example Use Case

Here's a complete example of setting up monitoring for a user's website:

```php
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Support\Facades\Auth;

// In your controller
public function createMonitor(Request $request)
{
    $validated = $request->validate([
        'url' => 'required|url',
        'interval' => 'required|integer|min:1',
        'ssl_check' => 'boolean',
    ]);

    $monitor = Monitor::create([
        'owner_type' => get_class(Auth::user()),
        'owner_id' => Auth::id(),
        'url' => $validated['url'],
        'interval' => $validated['interval'],
        'ssl_check' => $validated['ssl_check'] ?? true,
    ]);

    return response()->json([
        'message' => 'Monitor created successfully',
        'monitor' => $monitor,
    ]);
}
```

## Verifying Setup

After creating a monitor, verify everything is working:

1. **Check Database**: Ensure the monitor was created in the `monitors` table
2. **Run Manual Check**: Execute `php artisan monitor:check-status`
3. **Check History**: Verify entries were created in the `monitor_histories` table
4. **Check Scheduler**: Run `php artisan schedule:list` to see scheduled tasks

## Troubleshooting

### Monitors Not Being Checked

- Verify Laravel scheduler is running: `ps aux | grep schedule:run`
- Check the cron job is configured correctly
- Verify queue workers are running if using queues: `php artisan queue:work`

### No History Records

- Ensure the monitor's `status` field is set correctly
- Check for errors in Laravel logs: `tail -f storage/logs/laravel.log`
- Manually trigger a check: `php artisan monitor:check-status`

### SSL Checks Failing

- Verify the URL uses HTTPS protocol
- Ensure the SSL certificate is valid
- Check if your server can reach the monitored URL

## Next Steps

- Learn about [Architecture](../02-architecture/README.md) to understand how AppPulse works
- Explore [Usage Patterns](../03-usage/README.md) for advanced scenarios
- Review the [API Reference](../04-api/README.md) for detailed model and action documentation
