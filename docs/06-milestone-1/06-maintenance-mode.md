# Maintenance Mode

Maintenance mode allows you to suppress monitoring checks and alerts during planned maintenance windows.

## Overview

Use maintenance mode to:
- Prevent false alerts during planned downtime
- Schedule maintenance windows in advance
- Automatically resume monitoring after maintenance
- Track scheduled vs unscheduled downtime

## Enabling Maintenance Mode

### Ongoing Maintenance (No End Time)

```php
$monitor->update([
    'is_maintenance' => true,
]);

// Checks will be skipped until you disable maintenance mode
```

### Scheduled Maintenance Window

```php
$monitor->update([
    'is_maintenance' => true,
    'maintenance_start_at' => now()->addHours(2), // Starts in 2 hours
    'maintenance_end_at' => now()->addHours(4),   // Ends in 4 hours
]);

// Checks will be skipped only during the specified window
```

### Immediate Maintenance with End Time

```php
$monitor->update([
    'is_maintenance' => true,
    'maintenance_start_at' => now(),
    'maintenance_end_at' => now()->addHours(2),
]);

// Checks will be skipped for the next 2 hours
```

## Checking Maintenance Status

```php
if ($monitor->isInMaintenance()) {
    // Monitor is currently in maintenance mode
    // Checks will be skipped
}
```

## How It Works

When a monitor is in maintenance mode:

1. **Checks are Skipped**: The `CheckMonitor` action returns early
2. **No History Created**: No new `MonitorHistory` records are created
3. **No Events Dispatched**: No `MonitorUptimeChanged` events are triggered
4. **Alerts Suppressed**: No notifications are sent

## Automatic Maintenance Window Expiry

The system automatically detects when a maintenance window has ended:

```php
// This monitor is in maintenance, but the window has passed
$monitor->update([
    'is_maintenance' => true,
    'maintenance_start_at' => now()->subHours(3),
    'maintenance_end_at' => now()->subHour(),
]);

$monitor->isInMaintenance(); // Returns false
// Monitoring will resume automatically
```

## Disabling Maintenance Mode

```php
// Manually end maintenance
$monitor->update([
    'is_maintenance' => false,
    'maintenance_start_at' => null,
    'maintenance_end_at' => null,
]);
```

## Use Cases

### 1. Deploying New Code

```php
// Before deployment
$monitor->update([
    'is_maintenance' => true,
    'maintenance_start_at' => now(),
    'maintenance_end_at' => now()->addMinutes(30),
]);

// Deploy code...

// After deployment (if finished early)
$monitor->update(['is_maintenance' => false]);
```

### 2. Scheduled Server Maintenance

```php
// Schedule maintenance window for next Sunday
$monitor->update([
    'is_maintenance' => true,
    'maintenance_start_at' => now()->next('Sunday')->setTime(2, 0),
    'maintenance_end_at' => now()->next('Sunday')->setTime(4, 0),
]);
```

### 3. Emergency Maintenance

```php
// Immediate ongoing maintenance
$monitor->update(['is_maintenance' => true]);

// When issue is resolved
$monitor->update(['is_maintenance' => false]);
```

## Bulk Maintenance Operations

Enable maintenance for multiple monitors:

```php
use CleaniqueCoders\AppPulse\Models\Monitor;

// All monitors for a specific owner
Monitor::where('owner_type', User::class)
    ->where('owner_id', $userId)
    ->update([
        'is_maintenance' => true,
        'maintenance_start_at' => now(),
        'maintenance_end_at' => now()->addHours(2),
    ]);

// All monitors matching a URL pattern
Monitor::where('url', 'like', '%example.com%')
    ->update(['is_maintenance' => true]);
```

## Reporting on Maintenance

### Check Current Maintenance Status

```php
// Get all monitors currently in maintenance
$inMaintenance = Monitor::get()->filter(function ($monitor) {
    return $monitor->isInMaintenance();
});
```

### Upcoming Maintenance Windows

```php
// Get monitors with scheduled future maintenance
$upcoming = Monitor::where('is_maintenance', true)
    ->where('maintenance_start_at', '>', now())
    ->orderBy('maintenance_start_at')
    ->get();

foreach ($upcoming as $monitor) {
    echo sprintf(
        "Monitor: %s\nMaintenance: %s to %s\n\n",
        $monitor->url,
        $monitor->maintenance_start_at->toDayDateTimeString(),
        $monitor->maintenance_end_at->toDayDateTimeString()
    );
}
```

### Calculate Downtime

Distinguish between maintenance and actual downtime:

```php
use CleaniqueCoders\AppPulse\Enums\SiteStatus;
use CleaniqueCoders\AppPulse\Enums\Type;

// Get actual downtime (excluding maintenance)
$downtime = $monitor->histories()
    ->where('type', Type::UPTIME->value)
    ->where('status', SiteStatus::DOWN->value)
    ->whereBetween('created_at', [
        now()->subMonth(),
        now()
    ])
    ->whereNotExists(function ($query) use ($monitor) {
        // Exclude checks during maintenance windows
        // (This is a simplified example)
        $query->whereRaw('1=0'); // Implement your maintenance window logic
    })
    ->count();
```

## Best Practices

1. **Plan Ahead**: Schedule maintenance windows when setting them up
2. **Set End Times**: Always specify an end time to ensure monitoring resumes
3. **Communicate**: Inform your team about scheduled maintenance
4. **Review Regularly**: Check for stale maintenance modes that should have ended
5. **Document Reasons**: Keep records of why maintenance was needed

## Cleanup Stale Maintenance

Create an Artisan command to cleanup expired maintenance modes:

```php
<?php

namespace App\Console\Commands;

use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Console\Command;

class CleanupMaintenanceMode extends Command
{
    protected $signature = 'monitor:cleanup-maintenance';
    protected $description = 'Disable maintenance mode for expired windows';

    public function handle()
    {
        $count = Monitor::where('is_maintenance', true)
            ->where('maintenance_end_at', '<', now())
            ->update([
                'is_maintenance' => false,
                'maintenance_start_at' => null,
                'maintenance_end_at' => null,
            ]);

        $this->info("Cleaned up {$count} expired maintenance windows");
    }
}
```

Schedule it in `app/Console/Kernel.php`:

```php
$schedule->command('monitor:cleanup-maintenance')->hourly();
```

## Next Steps

- Configure [Alert Management](07-alert-management.md)
- Set up [Multi-Channel Notifications](05-notifications.md)
- Learn about [Retry Logic](04-retry-logic.md)
