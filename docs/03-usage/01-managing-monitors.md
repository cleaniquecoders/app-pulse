# Managing Monitors

Learn how to create, update, delete, and manage monitors in your application.

## Creating Monitors

### Basic Monitor

```php
use CleaniqueCoders\AppPulse\Models\Monitor;

$monitor = Monitor::create([
    'owner_type' => \App\Models\User::class,
    'owner_id' => auth()->id(),
    'url' => 'https://example.com',
    'interval' => 10, // Check every 10 minutes
    'ssl_check' => true,
]);
```

### With Owner Relationship

```php
$user = auth()->user();

$monitor = Monitor::create([
    'owner_type' => get_class($user),
    'owner_id' => $user->id,
    'url' => 'https://myapp.com',
    'interval' => 5,
    'ssl_check' => true,
]);
```

### Using Relationship

If your owner model has a `monitors()` relationship:

```php
// In User model
public function monitors()
{
    return $this->morphMany(Monitor::class, 'owner');
}

// Create monitor
$user->monitors()->create([
    'url' => 'https://site.com',
    'interval' => 15,
    'ssl_check' => false,
]);
```

## Updating Monitors

### Change Interval

```php
$monitor = Monitor::find($id);
$monitor->update(['interval' => 5]);
```

### Enable/Disable SSL Checking

```php
$monitor->update(['ssl_check' => false]);
```

### Toggle Monitoring

```php
use CleaniqueCoders\AppPulse\Actions\ToggleMonitoring;

$toggle = new ToggleMonitoring($monitor);
$toggle->execute();

// Or manually
$monitor->update(['status' => !$monitor->status]);
```

### Update URL

```php
$monitor->update(['url' => 'https://new-url.com']);
```

## Deleting Monitors

### Single Monitor

```php
$monitor = Monitor::find($id);
$monitor->delete();
// Associated history records are automatically deleted
```

### Bulk Delete

```php
// Delete all monitors for a user
Monitor::where('owner_type', \App\Models\User::class)
    ->where('owner_id', $userId)
    ->delete();
```

### Soft Deletes (Optional)

If you want soft deletes, add the trait to your extended model:

```php
namespace App\Models;

use CleaniqueCoders\AppPulse\Models\Monitor as BaseMonitor;
use Illuminate\Database\Eloquent\SoftDeletes;

class Monitor extends BaseMonitor
{
    use SoftDeletes;
}
```

## Querying Monitors

### By Owner

```php
$userMonitors = Monitor::where('owner_type', \App\Models\User::class)
    ->where('owner_id', $userId)
    ->get();
```

### Enabled Monitors

```php
use CleaniqueCoders\AppPulse\Enums\Status;

$activeMonitors = Monitor::where('status', Status::ENABLED->value)->get();
```

### With SSL Checking

```php
$sslMonitors = Monitor::where('ssl_check', true)->get();
```

### By URL Pattern

```php
$monitors = Monitor::where('url', 'LIKE', '%example.com%')->get();
```

### Recently Checked

```php
$recentlyChecked = Monitor::whereNotNull('last_checked_at')
    ->where('last_checked_at', '>=', now()->subHour())
    ->get();
```

## Validation

### Controller Example

```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url|max:255',
            'interval' => 'required|integer|min:1|max:1440',
            'ssl_check' => 'boolean',
        ]);

        $monitor = Monitor::create([
            'owner_type' => get_class(auth()->user()),
            'owner_id' => auth()->id(),
            'url' => $validated['url'],
            'interval' => $validated['interval'],
            'ssl_check' => $validated['ssl_check'] ?? true,
        ]);

        return response()->json($monitor, 201);
    }

    public function update(Request $request, Monitor $monitor)
    {
        $this->authorize('update', $monitor);

        $validated = $request->validate([
            'url' => 'url|max:255',
            'interval' => 'integer|min:1|max:1440',
            'ssl_check' => 'boolean',
            'status' => 'boolean',
        ]);

        $monitor->update($validated);

        return response()->json($monitor);
    }

    public function destroy(Monitor $monitor)
    {
        $this->authorize('delete', $monitor);

        $monitor->delete();

        return response()->json(['message' => 'Monitor deleted']);
    }
}
```

## Authorization

### Policy Example

```php
namespace App\Policies;

use App\Models\User;
use CleaniqueCoders\AppPulse\Models\Monitor;

class MonitorPolicy
{
    public function view(User $user, Monitor $monitor): bool
    {
        return $monitor->owner_id === $user->id &&
               $monitor->owner_type === get_class($user);
    }

    public function update(User $user, Monitor $monitor): bool
    {
        return $this->view($user, $monitor);
    }

    public function delete(User $user, Monitor $monitor): bool
    {
        return $this->view($user, $monitor);
    }
}
```

### Register Policy

In `AuthServiceProvider`:

```php
use App\Policies\MonitorPolicy;
use CleaniqueCoders\AppPulse\Models\Monitor;

protected $policies = [
    Monitor::class => MonitorPolicy::class,
];
```

## Best Practices

### Reasonable Intervals

Choose appropriate check intervals:

- **Critical services**: 1-5 minutes
- **Standard monitoring**: 10-15 minutes
- **Periodic checks**: 30-60 minutes

### URL Validation

Always validate URLs:

```php
$validated = $request->validate([
    'url' => [
        'required',
        'url',
        'max:255',
        function ($attribute, $value, $fail) {
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                $fail('The URL must be valid.');
            }
        },
    ],
]);
```

### Limit Per User

Prevent abuse by limiting monitors per user:

```php
public function store(Request $request)
{
    $user = auth()->user();

    if ($user->monitors()->count() >= 10) {
        return response()->json([
            'error' => 'Maximum monitor limit reached'
        ], 403);
    }

    // Create monitor...
}
```

### Queue Management

For users with many monitors, consider priority queues:

```php
if ($user->isPremium()) {
    CheckMonitorJob::dispatch($monitor)->onQueue('premium');
} else {
    CheckMonitorJob::dispatch($monitor)->onQueue('standard');
}
```
