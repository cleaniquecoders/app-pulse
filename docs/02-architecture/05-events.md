# Events

AppPulse uses Laravel's event system to notify your application when monitor status changes occur. This allows you to build custom notifications, logging, or other reactions to monitoring events.

## Available Events

### MonitorUptimeChanged

Dispatched when a monitor's uptime status changes (from UP to DOWN or vice versa).

**Namespace:** `CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged`

**Properties:**

- `Monitor $monitor` - The monitor instance
- `SiteStatus $status` - The new site status (UP or DOWN)

**When Dispatched:**

- When a site goes from UP to DOWN
- When a site goes from DOWN to UP
- When the first uptime check is performed

**Usage:**

Create a listener:

```php
namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use CleaniqueCoders\AppPulse\Enums\SiteStatus;
use Illuminate\Support\Facades\Log;

class HandleUptimeChange
{
    public function handle(MonitorUptimeChanged $event): void
    {
        $monitor = $event->monitor;
        $status = $event->status;

        if ($status === SiteStatus::DOWN) {
            // Send alert notification
            Log::error("Monitor {$monitor->url} is DOWN");

            // Notify owner
            $monitor->owner->notify(
                new SiteDownNotification($monitor)
            );
        } else {
            Log::info("Monitor {$monitor->url} is back UP");
        }
    }
}
```

Register in `EventServiceProvider`:

```php
use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use App\Listeners\HandleUptimeChange;

protected $listen = [
    MonitorUptimeChanged::class => [
        HandleUptimeChange::class,
    ],
];
```

---

### SslStatusChanged

Dispatched when a monitor's SSL certificate status changes.

**Namespace:** `CleaniqueCoders\AppPulse\Events\SslStatusChanged`

**Properties:**

- `Monitor $monitor` - The monitor instance
- `SslStatus $status` - The new SSL status

**When Dispatched:**

- When SSL certificate expires
- When SSL status changes from VALID to EXPIRED
- When SSL check fails
- When the first SSL check is performed

**Usage:**

Create a listener:

```php
namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\SslStatusChanged;
use CleaniqueCoders\AppPulse\Enums\SslStatus;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SslExpiringNotification;

class HandleSslStatusChange
{
    public function handle(SslStatusChanged $event): void
    {
        $monitor = $event->monitor;
        $status = $event->status;

        if ($status === SslStatus::EXPIRED) {
            // Alert: Certificate has expired
            Notification::send(
                $monitor->owner,
                new SslExpiredNotification($monitor)
            );
        }

        if ($status === SslStatus::VALID) {
            // Log successful SSL renewal
            Log::info("SSL certificate for {$monitor->url} is valid");
        }
    }
}
```

Register in `EventServiceProvider`:

```php
use CleaniqueCoders\AppPulse\Events\SslStatusChanged;
use App\Listeners\HandleSslStatusChange;

protected $listen = [
    SslStatusChanged::class => [
        HandleSslStatusChange::class,
    ],
];
```

## Registering Event Listeners

### Method 1: EventServiceProvider

In `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    \CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged::class => [
        \App\Listeners\SendUptimeAlert::class,
        \App\Listeners\LogUptimeChange::class,
    ],
    \CleaniqueCoders\AppPulse\Events\SslStatusChanged::class => [
        \App\Listeners\SendSslAlert::class,
    ],
];
```

### Method 2: Config File

In `config/app-pulse.php`:

```php
return [
    'events' => [
        \CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged::class => [
            \App\Listeners\CustomUptimeHandler::class,
        ],
        \CleaniqueCoders\AppPulse\Events\SslStatusChanged::class => [
            \App\Listeners\CustomSslHandler::class,
        ],
    ],
    // ...
];
```

### Method 3: Closure Listeners

In a service provider's `boot()` method:

```php
use Illuminate\Support\Facades\Event;
use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;

Event::listen(MonitorUptimeChanged::class, function ($event) {
    // Handle the event inline
    logger()->info('Monitor status changed', [
        'monitor' => $event->monitor->url,
        'status' => $event->status->value,
    ]);
});
```

## Common Use Cases

### Send Email Notifications

```php
namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use CleaniqueCoders\AppPulse\Enums\SiteStatus;
use Illuminate\Support\Facades\Mail;
use App\Mail\SiteDownAlert;

class SendEmailOnDowntime
{
    public function handle(MonitorUptimeChanged $event): void
    {
        if ($event->status === SiteStatus::DOWN) {
            Mail::to($event->monitor->owner->email)
                ->send(new SiteDownAlert($event->monitor));
        }
    }
}
```

### Slack Notifications

```php
namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use Illuminate\Support\Facades\Http;

class NotifySlackOnDowntime
{
    public function handle(MonitorUptimeChanged $event): void
    {
        $webhookUrl = config('services.slack.webhook_url');

        Http::post($webhookUrl, [
            'text' => "🚨 Monitor Alert: {$event->monitor->url} is {$event->status->label()}",
        ]);
    }
}
```

### Update Dashboard Statistics

```php
namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use App\Models\DashboardStats;

class UpdateDashboardStats
{
    public function handle(MonitorUptimeChanged $event): void
    {
        DashboardStats::updateMonitorStatus(
            $event->monitor,
            $event->status
        );
    }
}
```

### Log to External Service

```php
namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\SslStatusChanged;
use Illuminate\Support\Facades\Http;

class LogToDatadog
{
    public function handle(SslStatusChanged $event): void
    {
        Http::post('https://api.datadoghq.com/api/v1/events', [
            'title' => 'SSL Status Changed',
            'text' => "Monitor: {$event->monitor->url}",
            'tags' => ["status:{$event->status->value}"],
        ]);
    }
}
```

## Queued Event Listeners

For time-consuming operations, make your listeners implement `ShouldQueue`:

```php
namespace App\Listeners;

use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendUptimeAlert implements ShouldQueue
{
    public function handle(MonitorUptimeChanged $event): void
    {
        // This will be processed in the background
    }
}
```

## Event Properties

Both events provide access to:

- **Monitor Details**: URL, owner, interval, settings
- **Status Information**: Current status with label and description
- **Owner Relationship**: Access to the owning model
- **History**: Previous check results via `$monitor->histories()`

This allows you to build rich notification messages and make informed decisions in your event handlers.
