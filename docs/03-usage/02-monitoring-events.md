# Monitoring Events

Detailed guide on handling and responding to monitoring events.

## Event Listeners

### Uptime Alerts

Send email notifications when a site goes down:

```php
namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use CleaniqueCoders\AppPulse\Enums\SiteStatus;
use App\Notifications\SiteDownNotification;

class SendUptimeAlert
{
    public function handle(MonitorUptimeChanged $event): void
    {
        if ($event->status === SiteStatus::DOWN) {
            $event->monitor->owner->notify(
                new SiteDownNotification($event->monitor)
            );
        }
    }
}
```

### SSL Expiration Alerts

Alert when SSL certificates are expiring soon:

```php
namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\SslStatusChanged;
use CleaniqueCoders\AppPulse\Enums\SslStatus;
use App\Notifications\SslExpiringNotification;

class SendSslExpirationAlert
{
    public function handle(SslStatusChanged $event): void
    {
        $monitor = $event->monitor;
        $history = $monitor->getLatestHistory(Type::SSL);

        // Alert if expiring within 30 days
        if ($history->response_time <= 30) {
            $monitor->owner->notify(
                new SslExpiringNotification($monitor, $history->response_time)
            );
        }
    }
}
```

### Logging

Log all status changes:

```php
namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use Illuminate\Support\Facades\Log;

class LogUptimeChanges
{
    public function handle(MonitorUptimeChanged $event): void
    {
        Log::info('Monitor uptime changed', [
            'monitor_id' => $event->monitor->id,
            'url' => $event->monitor->url,
            'status' => $event->status->value,
            'owner' => $event->monitor->owner_id,
        ]);
    }
}
```

## Multiple Notification Channels

### Email + Slack

```php
namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use CleaniqueCoders\AppPulse\Enums\SiteStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class SendMultiChannelAlert
{
    public function handle(MonitorUptimeChanged $event): void
    {
        if ($event->status === SiteStatus::DOWN) {
            // Email
            Mail::to($event->monitor->owner->email)
                ->queue(new SiteDownAlert($event->monitor));

            // Slack
            Http::post(config('services.slack.webhook'), [
                'text' => "🚨 {$event->monitor->url} is DOWN",
            ]);
        }
    }
}
```

## Real-Time Updates

### Broadcasting Events

Make events broadcastable for real-time dashboard updates:

```php
namespace App\Events;

use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MonitorStatusUpdated implements ShouldBroadcast
{
    public function __construct(public Monitor $monitor) {}

    public function broadcastOn(): Channel
    {
        return new Channel("monitors.{$this->monitor->owner_id}");
    }
}
```

Listen in your existing event handlers and broadcast:

```php
public function handle(MonitorUptimeChanged $event): void
{
    broadcast(new MonitorStatusUpdated($event->monitor));
}
```

## Event Registration

In `EventServiceProvider`:

```php
protected $listen = [
    \CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged::class => [
        \App\Listeners\SendUptimeAlert::class,
        \App\Listeners\LogUptimeChanges::class,
        \App\Listeners\UpdateDashboard::class,
    ],
    \CleaniqueCoders\AppPulse\Events\SslStatusChanged::class => [
        \App\Listeners\SendSslExpirationAlert::class,
        \App\Listeners\LogSslChanges::class,
    ],
];
```
