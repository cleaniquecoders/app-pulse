# Models

AppPulse provides two main Eloquent models for managing monitors and their historical data.

## Monitor

The `Monitor` model represents a website or endpoint to be monitored.

### Namespace
```php
CleaniqueCoders\AppPulse\Models\Monitor
```

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `uuid` | string | Unique identifier for the monitor |
| `owner_id` | integer | ID of the owning model |
| `owner_type` | string | Fully qualified class name of the owner |
| `url` | string | URL to monitor |
| `status` | boolean | Monitoring enabled (1) or disabled (0) |
| `interval` | integer | Check interval in minutes |
| `ssl_check` | boolean | Whether SSL checking is enabled |
| `last_checked_at` | Carbon\|null | Timestamp of last check |

### Traits

- **HasFactory**: Laravel's factory trait for testing
- **InteractsWithUuid**: Automatically generates and manages UUIDs

### Fillable Attributes

```php
protected $fillable = [
    'uuid',
    'owner_id',
    'owner_type',
    'url',
    'status',
    'interval',
    'ssl_check',
    'last_checked_at',
];
```

### Casts

```php
protected $casts = [
    'status' => 'boolean',
];
```

### Relationships

#### owner()
```php
public function owner(): MorphTo
```

Polymorphic relationship to the owning model (e.g., User, Application).

**Example:**
```php
$monitor = Monitor::first();
$owner = $monitor->owner; // Returns User, Application, or any other owner model
```

#### histories()
```php
public function histories(): HasMany
```

One-to-many relationship with `MonitorHistory` records.

**Example:**
```php
$monitor = Monitor::first();
$histories = $monitor->histories()->latest()->get();
```

### Methods

#### hasHistory()
```php
public function hasHistory(Type $type): bool
```

Check if the monitor has any history records of a specific type.

**Parameters:**
- `$type` - Check type (Type::UPTIME or Type::SSL)

**Returns:** `bool`

**Example:**
```php
use CleaniqueCoders\AppPulse\Enums\Type;

$monitor = Monitor::first();

if ($monitor->hasHistory(Type::UPTIME)) {
    echo "Monitor has uptime history";
}
```

#### getLatestHistory()
```php
public function getLatestHistory(Type $type): MonitorHistory
```

Retrieve the most recent history record of a specific type.

**Parameters:**
- `$type` - Check type (Type::UPTIME or Type::SSL)

**Returns:** `MonitorHistory`

**Example:**
```php
use CleaniqueCoders\AppPulse\Enums\Type;

$monitor = Monitor::first();
$latestCheck = $monitor->getLatestHistory(Type::UPTIME);

echo "Response time: " . $latestCheck->response_time . "ms";
```

### Events

The Monitor model fires the following boot events:

#### deleting
When a monitor is deleted, all associated history records are also deleted (cascade delete).

### Usage Examples

#### Creating a Monitor
```php
use CleaniqueCoders\AppPulse\Models\Monitor;

$monitor = Monitor::create([
    'owner_type' => \App\Models\User::class,
    'owner_id' => 1,
    'url' => 'https://example.com',
    'interval' => 10,
    'ssl_check' => true,
]);
```

#### Querying Monitors
```php
// Find by UUID
$monitor = Monitor::where('uuid', $uuid)->first();

// Get all monitors for a user
$userMonitors = Monitor::where('owner_type', \App\Models\User::class)
    ->where('owner_id', $userId)
    ->get();

// Get enabled monitors
$activeMonitors = Monitor::where('status', true)->get();
```

#### Updating a Monitor
```php
$monitor = Monitor::first();
$monitor->update([
    'interval' => 5, // Change to 5 minutes
    'ssl_check' => false, // Disable SSL checking
]);
```

---

## MonitorHistory

The `MonitorHistory` model stores the results of each monitoring check.

### Namespace
```php
CleaniqueCoders\AppPulse\Models\MonitorHistory
```

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `uuid` | string | Unique identifier for the history record |
| `monitor_id` | integer | Foreign key to monitors table |
| `type` | string | Check type (uptime or ssl) |
| `status` | string | Check result status |
| `response_time` | float\|null | Response time in milliseconds |
| `error_message` | string\|null | Error message if check failed |
| `created_at` | Carbon | When the check was performed |
| `updated_at` | Carbon | Last update timestamp |

### Traits

- **HasFactory**: Laravel's factory trait for testing
- **InteractsWithUuid**: Automatically generates and manages UUIDs

### Fillable Attributes

```php
protected $fillable = [
    'uuid',
    'monitor_id',
    'type',
    'status',
    'response_time',
    'error_message',
];
```

### Relationships

#### monitor()
```php
public function monitor(): BelongsTo
```

Belongs to a `Monitor` record.

**Example:**
```php
$history = MonitorHistory::first();
$monitor = $history->monitor;

echo "Checked URL: " . $monitor->url;
```

### Usage Examples

#### Creating History Records
```php
use CleaniqueCoders\AppPulse\Models\MonitorHistory;
use CleaniqueCoders\AppPulse\Enums\Type;
use CleaniqueCoders\AppPulse\Enums\SiteStatus;
use Illuminate\Support\Str;

MonitorHistory::create([
    'uuid' => Str::orderedUuid(),
    'monitor_id' => $monitor->id,
    'type' => Type::UPTIME->value,
    'status' => SiteStatus::UP->value,
    'response_time' => 150.5,
    'error_message' => null,
]);
```

#### Querying History
```php
// Get all uptime checks for a monitor
$uptimeHistory = MonitorHistory::where('monitor_id', $monitorId)
    ->where('type', Type::UPTIME->value)
    ->orderBy('created_at', 'desc')
    ->get();

// Get failed checks
$failedChecks = MonitorHistory::where('status', SiteStatus::DOWN->value)
    ->with('monitor')
    ->get();

// Get recent checks with pagination
$recentChecks = MonitorHistory::where('monitor_id', $monitorId)
    ->latest()
    ->paginate(20);
```

#### Statistics Queries
```php
// Average response time for a monitor
$avgResponseTime = MonitorHistory::where('monitor_id', $monitorId)
    ->where('type', Type::UPTIME->value)
    ->avg('response_time');

// Uptime percentage (last 24 hours)
$totalChecks = MonitorHistory::where('monitor_id', $monitorId)
    ->where('type', Type::UPTIME->value)
    ->where('created_at', '>=', now()->subDay())
    ->count();

$upChecks = MonitorHistory::where('monitor_id', $monitorId)
    ->where('type', Type::UPTIME->value)
    ->where('status', SiteStatus::UP->value)
    ->where('created_at', '>=', now()->subDay())
    ->count();

$uptimePercentage = $totalChecks > 0 ? ($upChecks / $totalChecks) * 100 : 0;
```

## Model Factories

Both models include factory support for testing:

```php
use CleaniqueCoders\AppPulse\Models\Monitor;
use CleaniqueCoders\AppPulse\Models\MonitorHistory;

// Create a monitor with factory
$monitor = Monitor::factory()->create([
    'url' => 'https://test.example.com',
]);

// Create history records with factory
$history = MonitorHistory::factory()->create([
    'monitor_id' => $monitor->id,
]);

// Create multiple records
$monitors = Monitor::factory()->count(5)->create();
```
