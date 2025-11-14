# Advanced Patterns

Advanced usage patterns and real-world examples.

## Multi-Tenant Monitoring

### Tenant Isolation

```php
// In your Monitor model or trait
public function scopeForTenant($query, $tenantId)
{
    return $query->where('owner_type', \App\Models\Tenant::class)
        ->where('owner_id', $tenantId);
}

// Usage
$monitors = Monitor::forTenant($tenantId)->get();
```

### Tenant-Specific Queues

```php
$tenant = Tenant::find($tenantId);

foreach ($tenant->monitors as $monitor) {
    CheckMonitorJob::dispatch($monitor)
        ->onQueue("tenant-{$tenant->id}");
}
```

## Custom Monitor Types

### Database Monitoring

```php
namespace App\Actions;

use CleaniqueCoders\Traitify\Contracts\Execute;
use CleaniqueCoders\AppPulse\Models\Monitor;

class CheckDatabase implements Execute
{
    public function __construct(protected Monitor $monitor) {}

    public function execute(): self
    {
        try {
            DB::connection($this->monitor->url)->getPdo();
            $status = SiteStatus::UP;
        } catch (\Exception $e) {
            $status = SiteStatus::DOWN;
        }

        // Store result...

        return $this;
    }
}
```

### API Health Checks

```php
namespace App\Actions;

use Illuminate\Support\Facades\Http;

class CheckApiHealth implements Execute
{
    public function execute(): self
    {
        $response = Http::timeout(10)->get($this->monitor->url . '/health');

        $status = $response->successful() &&
                  $response->json('status') === 'healthy'
            ? SiteStatus::UP
            : SiteStatus::DOWN;

        // Store result...

        return $this;
    }
}
```

## Notification Throttling

### Prevent Alert Spam

```php
namespace App\Listeners;

use Illuminate\Support\Facades\Cache;

class SendUptimeAlert
{
    public function handle(MonitorUptimeChanged $event): void
    {
        if ($event->status === SiteStatus::DOWN) {
            $key = "alert-sent-{$event->monitor->id}";

            // Only send alert once per hour
            if (!Cache::has($key)) {
                $event->monitor->owner->notify(
                    new SiteDownNotification($event->monitor)
                );

                Cache::put($key, true, now()->addHour());
            }
        }
    }
}
```

## Escalation Policies

### Multi-Level Alerts

```php
namespace App\Listeners;

class EscalateDowntime
{
    public function handle(MonitorUptimeChanged $event): void
    {
        if ($event->status === SiteStatus::DOWN) {
            $downtimeMinutes = $this->calculateDowntime($event->monitor);

            match(true) {
                $downtimeMinutes >= 60 => $this->notifyExecutives($event->monitor),
                $downtimeMinutes >= 30 => $this->notifyManagers($event->monitor),
                $downtimeMinutes >= 5 => $this->notifyTeam($event->monitor),
                default => $this->notifyOwner($event->monitor),
            };
        }
    }
}
```

## Integration Examples

### Webhook Integration

```php
namespace App\Listeners;

class SendWebhook
{
    public function handle(MonitorUptimeChanged $event): void
    {
        $webhookUrl = $event->monitor->owner->webhook_url;

        if ($webhookUrl) {
            Http::post($webhookUrl, [
                'event' => 'monitor.uptime.changed',
                'monitor' => [
                    'id' => $event->monitor->id,
                    'url' => $event->monitor->url,
                    'status' => $event->status->value,
                ],
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }
}
```

### Status Page Updates

```php
namespace App\Listeners;

class UpdateStatusPage
{
    public function handle(MonitorUptimeChanged $event): void
    {
        $statusPage = StatusPage::where('monitor_id', $event->monitor->id)->first();

        if ($statusPage) {
            $statusPage->update([
                'status' => $event->status->value,
                'last_checked_at' => now(),
            ]);
        }
    }
}
```

## Performance Optimization

### Batch Processing

```php
$monitors = Monitor::where('status', true)->get();

$chunks = $monitors->chunk(50);

foreach ($chunks as $chunk) {
    dispatch(function () use ($chunk) {
        foreach ($chunk as $monitor) {
            (new CheckMonitor($monitor))->execute();
        }
    })->onQueue('bulk-processing');
}
```

### Caching Results

```php
use Illuminate\Support\Facades\Cache;

$uptimePercent = Cache::remember(
    "monitor-{$monitor->id}-uptime-percent",
    now()->addMinutes(15),
    function () use ($monitor) {
        return $this->calculateUptimePercentage($monitor);
    }
);
```

## Monitoring Groups

### Group Management

```php
// Create a monitor group
$group = MonitorGroup::create([
    'name' => 'Production Servers',
    'owner_type' => \App\Models\User::class,
    'owner_id' => auth()->id(),
]);

// Assign monitors to group
$group->monitors()->attach($monitorIds);

// Check all monitors in group
foreach ($group->monitors as $monitor) {
    CheckMonitorJob::dispatch($monitor);
}
```
