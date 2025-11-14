# Actions

Actions in AppPulse encapsulate business logic for specific operations. All actions implement the `Execute` contract for consistency.

## Action Pattern

Each action follows this structure:

```php
use CleaniqueCoders\Traitify\Contracts\Execute;

class SomeAction implements Execute
{
    public function __construct(/* dependencies */) {}

    public function execute(): self
    {
        // Business logic here
        return $this;
    }
}
```

## Available Actions

### CheckMonitor

Performs uptime checks for a monitor by making an HTTP request to the monitored URL.

**Namespace:** `CleaniqueCoders\AppPulse\Actions\CheckMonitor`

**Constructor Parameters:**

- `Monitor $monitor` - The monitor instance to check

**Process:**

1. Makes HTTP GET request to the monitor's URL
2. Measures response time in milliseconds
3. Determines site status (UP/DOWN)
4. Creates or updates monitor history
5. Dispatches `MonitorUptimeChanged` event if status changed

**Usage:**

```php
use CleaniqueCoders\AppPulse\Actions\CheckMonitor;
use CleaniqueCoders\AppPulse\Models\Monitor;

$monitor = Monitor::first();
$action = new CheckMonitor($monitor);
$action->execute();
```

**Example with Job:**

```php
use CleaniqueCoders\AppPulse\Jobs\CheckMonitorJob;

CheckMonitorJob::dispatch($monitor);
```

**Status Determination:**

- `SiteStatus::UP` - HTTP response is 200-299
- `SiteStatus::DOWN` - HTTP error or non-2xx response

**Data Captured:**

- Response time (milliseconds)
- Status (UP/DOWN)
- Error message (if failed)
- Timestamp

---

### CheckSsl

Validates SSL certificates for HTTPS-enabled monitors.

**Namespace:** `CleaniqueCoders\AppPulse\Actions\CheckSsl`

**Constructor Parameters:**

- `Monitor $monitor` - The monitor instance to check
- `bool $force = false` - Force check even if SSL checking is disabled

**Process:**

1. Extracts hostname from monitor URL
2. Retrieves SSL certificate using `Spatie\SslCertificate`
3. Checks certificate validity and expiration
4. Calculates days until expiration
5. Creates monitor history with SSL status
6. Dispatches `SslStatusChanged` event if status changed

**Usage:**

```php
use CleaniqueCoders\AppPulse\Actions\CheckSsl;
use CleaniqueCoders\AppPulse\Models\Monitor;

$monitor = Monitor::first();
$action = new CheckSsl($monitor);
$action->execute();

// Force SSL check regardless of ssl_check setting
$action = new CheckSsl($monitor, force: true);
$action->execute();
```

**Testing with Mock Certificate:**

```php
use Spatie\SslCertificate\SslCertificate;

$mockCertificate = SslCertificate::createForHostName('example.com');

$action = new CheckSsl($monitor);
$action->mock($mockCertificate)->execute();
```

**SSL Status Determination:**

- `SslStatus::VALID` - Certificate is valid and not expired
- `SslStatus::EXPIRED` - Certificate has expired
- `SslStatus::NOT_YET_VALID` - Certificate is not yet valid
- `SslStatus::FAILED_CHECK` - Unable to retrieve or validate certificate
- `SslStatus::FAILED_PARSE` - Failed to parse certificate data

**Data Captured:**

- SSL status
- Days until expiration (stored in response_time field)
- Error message (if failed)
- Timestamp

---

### ToggleMonitoring

Enables or disables monitoring for a specific monitor.

**Namespace:** `CleaniqueCoders\AppPulse\Actions\ToggleMonitoring`

**Constructor Parameters:**

- `Monitor $monitor` - The monitor instance to toggle

**Process:**

1. Toggles the monitor's `status` field
2. Updates the monitor in the database

**Usage:**

```php
use CleaniqueCoders\AppPulse\Actions\ToggleMonitoring;
use CleaniqueCoders\AppPulse\Models\Monitor;

$monitor = Monitor::first();

// Toggle monitoring status
$action = new ToggleMonitoring($monitor);
$action->execute();

// Check new status
echo $monitor->fresh()->status; // 1 or 0
```

**Status Transition:**

- `Status::ENABLED (1)` → `Status::DISABLED (0)`
- `Status::DISABLED (0)` → `Status::ENABLED (1)`

---

## Creating Custom Actions

You can create custom actions for additional monitoring capabilities:

```php
namespace App\Actions;

use CleaniqueCoders\AppPulse\Models\Monitor;
use CleaniqueCoders\Traitify\Contracts\Execute;

class CheckDnsRecords implements Execute
{
    public function __construct(protected Monitor $monitor) {}

    public function execute(): self
    {
        // Custom DNS checking logic
        $host = parse_url($this->monitor->url, PHP_URL_HOST);
        $records = dns_get_record($host, DNS_A + DNS_AAAA);

        // Store results
        // Dispatch events

        return $this;
    }
}
```

## Action Design Principles

### Single Responsibility

Each action handles one specific operation (check uptime, check SSL, toggle status).

### Immutable State

Actions receive their dependencies through the constructor and don't modify external state unexpectedly.

### Chainability

Actions return `$this` to allow method chaining if needed.

### Event-Driven

Actions dispatch events when significant state changes occur, allowing for extensibility.

### Error Handling

Actions gracefully handle exceptions and log error details in monitor history.
