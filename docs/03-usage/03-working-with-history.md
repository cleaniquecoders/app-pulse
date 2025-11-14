# Working with History

Query and analyze monitor check history for insights and reporting.

## Accessing History

### Latest Check

```php
use CleaniqueCoders\AppPulse\Enums\Type;

$monitor = Monitor::first();
$latestUptime = $monitor->getLatestHistory(Type::UPTIME);
$latestSsl = $monitor->getLatestHistory(Type::SSL);
```

### All History

```php
$history = $monitor->histories()->latest()->get();
```

### Filtered by Type

```php
$uptimeHistory = $monitor->histories()
    ->where('type', Type::UPTIME->value)
    ->orderBy('created_at', 'desc')
    ->get();
```

## Statistics

### Uptime Percentage

```php
use CleaniqueCoders\AppPulse\Enums\SiteStatus;

$totalChecks = $monitor->histories()
    ->where('type', Type::UPTIME->value)
    ->where('created_at', '>=', now()->subMonth())
    ->count();

$upChecks = $monitor->histories()
    ->where('type', Type::UPTIME->value)
    ->where('status', SiteStatus::UP->value)
    ->where('created_at', '>=', now()->subMonth())
    ->count();

$uptimePercent = $totalChecks > 0 ? ($upChecks / $totalChecks) * 100 : 0;
```

### Average Response Time

```php
$avgResponseTime = $monitor->histories()
    ->where('type', Type::UPTIME->value)
    ->where('created_at', '>=', now()->subDay())
    ->avg('response_time');
```

### Downtime Incidents

```php
$downtimeIncidents = $monitor->histories()
    ->where('type', Type::UPTIME->value)
    ->where('status', SiteStatus::DOWN->value)
    ->where('created_at', '>=', now()->subWeek())
    ->get();
```

## Reporting

### Daily Summary

```php
$summary = MonitorHistory::where('monitor_id', $monitor->id)
    ->where('type', Type::UPTIME->value)
    ->whereDate('created_at', today())
    ->selectRaw('
        COUNT(*) as total_checks,
        AVG(response_time) as avg_response_time,
        MIN(response_time) as min_response_time,
        MAX(response_time) as max_response_time,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as down_count
    ', [SiteStatus::DOWN->value])
    ->first();
```

### SSL Certificate Status

```php
$sslHistory = $monitor->histories()
    ->where('type', Type::SSL->value)
    ->latest()
    ->first();

if ($sslHistory) {
    $daysUntilExpiry = $sslHistory->response_time;
    $status = $sslHistory->status;
}
```

## Cleanup Old History

### Prune Old Records

```php
// Delete history older than 90 days
MonitorHistory::where('created_at', '<', now()->subDays(90))->delete();
```

### Scheduled Cleanup

In `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        MonitorHistory::where('created_at', '<', now()->subDays(90))->delete();
    })->daily();
}
```

## Data Visualization

### Chart Data

```php
$chartData = $monitor->histories()
    ->where('type', Type::UPTIME->value)
    ->where('created_at', '>=', now()->subWeek())
    ->orderBy('created_at')
    ->get()
    ->map(function ($history) {
        return [
            'timestamp' => $history->created_at->format('Y-m-d H:i'),
            'response_time' => $history->response_time,
            'status' => $history->status,
        ];
    });
```
