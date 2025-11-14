# Testing

Guidelines for testing AppPulse.

## Running Tests

```bash
composer test
```

## Test Structure

AppPulse uses [Pest PHP](https://pestphp.com/) for testing.

### Unit Tests

Test individual actions and models:

```php
it('checks monitor uptime', function () {
    $monitor = Monitor::factory()->create([
        'url' => 'https://example.com',
    ]);

    $action = new CheckMonitor($monitor);
    $action->execute();

    expect($monitor->hasHistory(Type::UPTIME))->toBeTrue();
});
```

### Feature Tests

Test complete workflows:

```php
it('dispatches monitor check jobs', function () {
    Queue::fake();

    $monitor = Monitor::factory()->create();

    CheckMonitorJob::dispatch($monitor);

    Queue::assertPushed(CheckMonitorJob::class);
});
```

## Writing Tests

See the `tests/` directory for examples.

## Code Coverage

Run tests with coverage:

```bash
composer test -- --coverage
```
