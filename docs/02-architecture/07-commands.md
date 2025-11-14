# Commands

AppPulse provides Artisan commands for manual monitor operations and testing.

## CheckMonitorStatusCommand

Manually trigger monitor checks for all enabled monitors.

**Command:** `monitor:check-status`

**Signature:**

```bash
php artisan monitor:check-status
    {--chunk-size=100 : The number of monitors to process per chunk}
    {--queue=default : The queue to use for dispatching the jobs}
    {--force-check-ssl : Force SSL checks regardless of monitor settings}
```

### Options

#### --chunk-size

**Default:** `100`

Number of monitors to process per batch.

**Example:**

```bash
# Process 50 monitors at a time
php artisan monitor:check-status --chunk-size=50
```

#### --queue

**Default:** `default`

Specify which queue to dispatch jobs to.

**Example:**

```bash
# Use monitoring queue
php artisan monitor:check-status --queue=monitoring
```

#### --force-check-ssl

**Default:** `false`

Force SSL checks even for monitors with `ssl_check` disabled.

**Example:**

```bash
# Check SSL for all monitors
php artisan monitor:check-status --force-check-ssl
```

### Behavior

The command performs the following operations:

1. **Fetches Enabled Monitors:** Retrieves all monitors where `status = ENABLED`
2. **Chunks Processing:** Processes monitors in configurable batches
3. **Interval Checking:** Only dispatches jobs for monitors whose interval has elapsed
4. **Job Dispatch:** Dispatches `CheckMonitorJob` and optionally `CheckSslJob` to the queue
5. **First-Time Checks:** Immediately checks monitors with no history

### Usage Examples

#### Basic Check

```bash
php artisan monitor:check-status
```

This will:

- Process 100 monitors per chunk
- Use the `default` queue
- Only check SSL for monitors with `ssl_check` enabled

#### High Volume

```bash
php artisan monitor:check-status --chunk-size=500 --queue=monitoring
```

For high-volume monitoring with dedicated queue.

#### SSL Audit

```bash
php artisan monitor:check-status --force-check-ssl --queue=ssl-checks
```

Force SSL checks for all monitors, useful for security audits.

#### Quick Test

```bash
# Test with small batch
php artisan monitor:check-status --chunk-size=5
```

### Scheduling

The command is automatically scheduled based on your config settings. In `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $interval = config('app-pulse.scheduler.interval', 1);

    $schedule->command('monitor:check-status')
        ->everyMinutes($interval);
}
```

### Manual Execution

You can manually trigger checks in your application code:

```php
use Illuminate\Support\Facades\Artisan;

// Run the command
Artisan::call('monitor:check-status', [
    '--chunk-size' => 50,
    '--queue' => 'high-priority',
]);

// Get output
$output = Artisan::output();
```

### Testing

Test the command in your application:

```php
use Tests\TestCase;

class CheckMonitorStatusCommandTest extends TestCase
{
    public function test_command_dispatches_jobs()
    {
        $monitor = Monitor::factory()->create(['status' => Status::ENABLED]);

        $this->artisan('monitor:check-status')
            ->expectsOutput("Monitor checks have been dispatched to the 'default' queue.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('jobs', [
            // Assert job was queued
        ]);
    }
}
```

### Output

Successful execution outputs:

```
Monitor checks have been dispatched to the 'default' queue.
```

### Error Handling

The command handles errors gracefully:

- **No Monitors:** Completes successfully with no action
- **Queue Errors:** Laravel's queue system handles dispatch failures
- **Database Errors:** Exceptions are logged and reported

### Performance Tips

#### Large Datasets

For thousands of monitors:

```bash
php artisan monitor:check-status --chunk-size=200 --queue=bulk-monitoring
```

Ensure you have sufficient queue workers running.

#### Parallel Processing

Run multiple queue workers for faster processing:

```bash
# Terminal 1
php artisan queue:work --queue=monitoring --sleep=1

# Terminal 2
php artisan queue:work --queue=monitoring --sleep=1

# Terminal 3
php artisan queue:work --queue=monitoring --sleep=1
```

#### Memory Management

For very large datasets, consider increasing PHP memory:

```bash
php -d memory_limit=512M artisan monitor:check-status
```

### Integration with Other Commands

Chain with other operations:

```bash
# Check monitors and then process queue
php artisan monitor:check-status && php artisan queue:work --stop-when-empty
```

### Debugging

Enable verbose output for debugging:

```bash
php artisan monitor:check-status -v
```

Or use `--debug`:

```bash
php artisan monitor:check-status --debug
```

### Cron Setup

For production, add to crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

The Laravel scheduler will automatically run the command based on your configured interval.

## Creating Custom Commands

You can create additional commands for specific monitoring needs:

```php
namespace App\Console\Commands;

use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Console\Command;

class CheckCriticalMonitors extends Command
{
    protected $signature = 'monitor:check-critical';
    protected $description = 'Check only critical monitors';

    public function handle(): void
    {
        $monitors = Monitor::where('is_critical', true)
            ->where('status', true)
            ->get();

        foreach ($monitors as $monitor) {
            // Dispatch with high priority
            CheckMonitorJob::dispatch($monitor)->onQueue('critical');
        }

        $this->info("Critical monitors checked");
    }
}
```
