# Events API

Complete API reference for AppPulse events.

## MonitorUptimeChanged

**Namespace:** `CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged`

**Properties:**
- `Monitor $monitor` - The monitor instance
- `SiteStatus $status` - The new site status

**Usage:**
```php
use CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged;

Event::listen(MonitorUptimeChanged::class, function ($event) {
    $monitor = $event->monitor;
    $status = $event->status;
});
```

---

## SslStatusChanged

**Namespace:** `CleaniqueCoders\AppPulse\Events\SslStatusChanged`

**Properties:**
- `Monitor $monitor` - The monitor instance
- `SslStatus $status` - The new SSL status

**Usage:**
```php
use CleaniqueCoders\AppPulse\Events\SslStatusChanged;

Event::listen(SslStatusChanged::class, function ($event) {
    $monitor = $event->monitor;
    $status = $event->status;
});
```
