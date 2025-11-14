# Response Time Tracking

AppPulse v1.2.0 includes enhanced response time tracking and analytics capabilities.

## Overview

Response time tracking helps you:
- Monitor application performance over time
- Identify performance degradation
- Set and track SLAs
- Detect anomalies and trends

## Configuration

### Set Response Time Threshold

Configure a threshold to receive alerts when response time exceeds acceptable limits:

```php
$monitor = Monitor::create([
    'url' => 'https://example.com',
    'response_time_threshold' => 500, // milliseconds
]);
```

When response time exceeds this threshold, a `MonitorResponseTimeDegraded` event is dispatched.

## Response Time Analytics

### Average Response Time

```php
// Get average response time for all checks
$avgTime = $monitor->getAverageResponseTime();

// Get average for last 100 checks
$recentAvg = $monitor->getAverageResponseTime(100);

// Get average for last 24 hours
$last24Hours = $monitor->histories()
    ->where('type', 'uptime')
    ->where('created_at', '>=', now()->subDay())
    ->avg('response_time');
```

### Minimum and Maximum Response Times

```php
// Get minimum response time
$minTime = $monitor->getMinResponseTime();

// Get maximum response time
$maxTime = $monitor->getMaxResponseTime();

// Get min/max for last 1000 checks
$minRecent = $monitor->getMinResponseTime(1000);
$maxRecent = $monitor->getMaxResponseTime(1000);
```

### Check for Performance Degradation

```php
// Check if a specific response time is degraded
if ($monitor->isResponseTimeDegraded($responseTime)) {
    // Alert or take action
}
```

## Listening to Performance Alerts

Create a listener for performance degradation events:

```php
<?php

namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorResponseTimeDegraded;

class NotifyPerformanceDegradation
{
    public function handle(MonitorResponseTimeDegraded $event): void
    {
        logger()->warning('Performance Degraded', [
            'monitor' => $event->monitor->url,
            'response_time' => $event->responseTime,
            'threshold' => $event->threshold,
        ]);

        // Send custom notification, trigger remediation, etc.
    }
}
```

Register in `EventServiceProvider`:

```php
use CleaniqueCoders\AppPulse\Events\MonitorResponseTimeDegraded;
use App\Listeners\NotifyPerformanceDegradation;

protected $listen = [
    MonitorResponseTimeDegraded::class => [
        NotifyPerformanceDegradation::class,
    ],
];
```

## Analyzing Response Time Trends

### Query Response Times

```php
use CleaniqueCoders\AppPulse\Enums\Type;

// Get all uptime checks with response times
$history = $monitor->histories()
    ->where('type', Type::UPTIME->value)
    ->whereNotNull('response_time')
    ->orderBy('created_at')
    ->get();

// Calculate hourly averages
$hourlyAverages = $history->groupBy(function ($item) {
    return $item->created_at->format('Y-m-d H:00');
})->map(function ($group) {
    return $group->avg('response_time');
});
```

### Performance Reports

```php
// Last 24 hours performance summary
$summary = [
    'average' => $monitor->getAverageResponseTime(),
    'minimum' => $monitor->getMinResponseTime(),
    'maximum' => $monitor->getMaxResponseTime(),
    'total_checks' => $monitor->histories()
        ->where('type', Type::UPTIME->value)
        ->where('created_at', '>=', now()->subDay())
        ->count(),
    'degraded_checks' => $monitor->histories()
        ->where('type', Type::UPTIME->value)
        ->where('created_at', '>=', now()->subDay())
        ->where('response_time', '>', $monitor->response_time_threshold)
        ->count(),
];
```

## Integration with Monitoring Tools

Export response time data for external monitoring tools:

```php
// Export to Prometheus format
Route::get('/metrics', function () {
    $monitors = Monitor::all();

    $metrics = [];
    foreach ($monitors as $monitor) {
        $metrics[] = sprintf(
            'app_pulse_response_time{monitor="%s"} %f',
            $monitor->url,
            $monitor->getAverageResponseTime(10) // Last 10 checks
        );
    }

    return response(implode("\n", $metrics))
        ->header('Content-Type', 'text/plain');
});
```

## Best Practices

1. **Set Realistic Thresholds**: Base thresholds on actual performance data
2. **Monitor Trends**: Look for gradual degradation, not just sudden spikes
3. **Consider Context**: Different endpoints may have different acceptable response times
4. **Use Alert Throttling**: Prevent alert fatigue for temporary spikes
5. **Regular Review**: Periodically review and adjust thresholds

## Next Steps

- Configure [Retry Logic](04-retry-logic.md)
- Set up [Multi-Channel Notifications](05-notifications.md)
- Learn about [Alert Management](07-alert-management.md)
