[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/app-pulse.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/app-pulse) [![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/app-pulse/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/app-pulse/actions?query=workflow%3Arun-tests+branch%3Amain) [![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/app-pulse/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/app-pulse/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain) [![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/app-pulse.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/app-pulse)

# AppPulse

A comprehensive Laravel package for monitoring website uptime and SSL certificates with event-driven notifications.

## Features

- **Uptime Monitoring** - Track website availability and response times
- **SSL Certificate Validation** - Monitor certificate expiration
- **Event-Driven Notifications** - React to status changes
- **Queue-Based Processing** - Efficient background monitoring
- **Historical Data** - Store and analyze monitoring results
- **Polymorphic Relationships** - Monitor any model in your application

## Requirements

- PHP 8.2, 8.3, or 8.4
- Laravel 10.x, 11.x, or 12.x

## Installation

Install via Composer:

```bash
composer require cleaniquecoders/app-pulse
```

Publish configuration and migrations:

```bash
php artisan vendor:publish --tag="app-pulse-config"
php artisan vendor:publish --tag="app-pulse-migrations"
php artisan migrate
```

Set up Laravel scheduler by adding to your crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Quick Start

Create a monitor:

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

Run checks manually:

```bash
php artisan monitor:check-status
```

Listen to events:

```php
// In EventServiceProvider
protected $listen = [
    \CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged::class => [
        \App\Listeners\SendUptimeAlert::class,
    ],
    \CleaniqueCoders\AppPulse\Events\SslStatusChanged::class => [
        \App\Listeners\SendSslAlert::class,
    ],
];
```

## Documentation

For complete documentation, visit the [docs](docs/README.md) directory:

- **[Getting Started](docs/01-getting-started/README.md)** - Installation and configuration
- **[Architecture](docs/02-architecture/README.md)** - How AppPulse works
- **[Usage Guide](docs/03-usage/README.md)** - Practical examples
- **[API Reference](docs/04-api/README.md)** - Complete API documentation
- **[Development](docs/05-development/README.md)** - Contributing guidelines

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Please see [CONTRIBUTING](docs/05-development/02-contributing.md) for details.

## Security

For security concerns, review our [security policy](../../security/policy).

## Credits

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
