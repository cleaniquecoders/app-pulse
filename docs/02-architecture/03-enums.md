# Enums

AppPulse uses PHP enums to define status types and check categories, providing type-safe constants throughout the application.

## Available Enums

### Status

Defines the monitoring status of a site - enabled or disabled.

**Namespace:** `CleaniqueCoders\AppPulse\Enums\Status`

**Type:** `int`

**Cases:**

- `ENABLED = 1` - Monitoring is enabled
- `DISABLED = 0` - Monitoring is disabled

**Methods:**

- `label(): string` - Returns human-readable label ("Enabled" or "Disabled")
- `description(): string` - Returns description of the status

**Usage:**

```php
use CleaniqueCoders\AppPulse\Enums\Status;

$monitor->status = Status::ENABLED->value;

if ($status === Status::ENABLED) {
    echo $status->label(); // "Enabled"
}
```

---

### SiteStatus

Defines the operational status of a monitored site.

**Namespace:** `CleaniqueCoders\AppPulse\Enums\SiteStatus`

**Type:** `string`

**Cases:**

- `UP = 'up'` - Site is operational and accessible
- `DOWN = 'down'` - Site is not operational and inaccessible

**Methods:**

- `label(): string` - Returns "Up" or "Down"
- `description(): string` - Returns description of the site status

**Usage:**

```php
use CleaniqueCoders\AppPulse\Enums\SiteStatus;

$status = SiteStatus::UP;

echo $status->label(); // "Up"
echo $status->description(); // "The site is currently up and running."
```

---

### SslStatus

Represents the possible SSL certificate statuses.

**Namespace:** `CleaniqueCoders\AppPulse\Enums\SslStatus`

**Type:** `string`

**Cases:**

- `EXPIRED = 'ssl_expired'` - SSL certificate is expired
- `VALID = 'ssl_valid'` - SSL certificate is valid
- `NOT_YET_VALID = 'ssl_not_yet_valid'` - SSL certificate is not yet valid
- `UNCHECKED = 'ssl_unchecked'` - SSL check has not been performed
- `FAILED_PARSE = 'ssl_failed_parse'` - Failed to parse SSL certificate data
- `FAILED_CHECK = 'ssl_failed_check'` - Failed to check SSL certificate status

**Methods:**

- `label(): string` - Returns human-readable label
- `description(): string` - Returns description of the SSL status

**Usage:**

```php
use CleaniqueCoders\AppPulse\Enums\SslStatus;

$sslStatus = SslStatus::VALID;

echo $sslStatus->label(); // "Valid"

// Check if SSL is valid
if ($sslStatus === SslStatus::VALID) {
    echo "SSL certificate is valid";
}
```

---

### Type

Represents the type of monitoring check being performed.

**Namespace:** `CleaniqueCoders\AppPulse\Enums\Type`

**Type:** `string`

**Cases:**

- `UPTIME = 'uptime'` - Uptime monitoring type
- `SSL = 'ssl'` - SSL certificate monitoring type

**Methods:**

- `label(): string` - Returns "Uptime" or "SSL"
- `description(): string` - Returns description of the check type

**Usage:**

```php
use CleaniqueCoders\AppPulse\Enums\Type;

// Check for uptime history
if ($monitor->hasHistory(Type::UPTIME)) {
    $latestCheck = $monitor->getLatestHistory(Type::UPTIME);
}

// Check for SSL history
if ($monitor->hasHistory(Type::SSL)) {
    $sslCheck = $monitor->getLatestHistory(Type::SSL);
}
```

## Using Enums in Queries

Enums are particularly useful when querying monitor histories:

```php
use CleaniqueCoders\AppPulse\Models\MonitorHistory;
use CleaniqueCoders\AppPulse\Enums\Type;
use CleaniqueCoders\AppPulse\Enums\SiteStatus;

// Get all uptime checks
$uptimeChecks = MonitorHistory::where('type', Type::UPTIME->value)->get();

// Get all failed checks
$failedChecks = MonitorHistory::where('status', SiteStatus::DOWN->value)->get();
```

## Enum Traits

All enums in AppPulse implement:

- `InteractsWithEnum` trait from the `cleaniquecoders/traitify` package
- `Enum` contract for consistency

This provides additional helper methods for working with enums throughout the application.
