# AppPulse Documentation

Welcome to the comprehensive documentation for AppPulse - a Laravel package for monitoring website uptime and SSL certificates.

## Quick Navigation

### 🚀 Getting Started
New to AppPulse? Start here to get up and running quickly.

- [Overview](01-getting-started/01-overview.md) - Introduction and features
- [Installation](01-getting-started/02-installation.md) - Install via Composer
- [Configuration](01-getting-started/03-configuration.md) - Setup and configuration
- [Quick Start](01-getting-started/04-quick-start.md) - Create your first monitor

### 🏗️ Architecture
Understand how AppPulse works under the hood.

- [Overview](02-architecture/01-overview.md) - Design principles and data flow
- [Models](02-architecture/02-models.md) - Database models and relationships
- [Enums](02-architecture/03-enums.md) - Status types and constants
- [Actions](02-architecture/04-actions.md) - Business logic classes
- [Events](02-architecture/05-events.md) - Event system and listeners
- [Jobs](02-architecture/06-jobs.md) - Background job processing
- [Commands](02-architecture/07-commands.md) - Artisan commands

### 📖 Usage Guide
Practical examples and patterns for daily use.

- [Managing Monitors](03-usage/01-managing-monitors.md) - CRUD operations
- [Monitoring Events](03-usage/02-monitoring-events.md) - Event handling
- [Working with History](03-usage/03-working-with-history.md) - Querying and analytics
- [Scheduler Configuration](03-usage/04-scheduler-configuration.md) - Automated checks
- [Advanced Patterns](03-usage/05-advanced-patterns.md) - Multi-tenant and custom workflows

### 📚 API Reference
Complete API documentation for developers.

- [Models API](04-api/01-models-api.md) - Model properties and methods
- [Actions API](04-api/02-actions-api.md) - Action class reference
- [Enums API](04-api/03-enums-api.md) - Available enum values
- [Events API](04-api/04-events-api.md) - Event class reference

### 🛠️ Development
Contributing and testing guidelines.

- [Testing](05-development/01-testing.md) - Running tests
- [Contributing](05-development/02-contributing.md) - Contribution guidelines
- [Code Style](05-development/03-code-style.md) - Coding standards

## What is AppPulse?

AppPulse is a comprehensive monitoring solution for Laravel applications that provides:

- **Uptime Monitoring** - Track website availability and response times
- **SSL Certificate Validation** - Monitor certificate expiration
- **Event-Driven Notifications** - React to status changes
- **Flexible Configuration** - Customize check intervals and queues
- **Historical Data** - Store and analyze monitoring results

## Key Features

### Monitor Any Website
Track uptime and performance for any publicly accessible URL.

### SSL Certificate Monitoring
Stay ahead of certificate expirations with automated SSL checks.

### Polymorphic Relationships
Monitor resources for any model in your application (Users, Teams, Applications, etc.).

### Queue-Based Processing
Efficient background processing using Laravel's queue system.

### Event System
Build custom notifications and workflows using Laravel events.

### Comprehensive History
Store detailed check results for analytics and reporting.

## System Requirements

- PHP 8.2, 8.3, or 8.4
- Laravel 10.x, 11.x, or 12.x
- Database (MySQL, PostgreSQL, SQLite, or any Laravel-supported database)

## Quick Example

```php
use CleaniqueCoders\AppPulse\Models\Monitor;

// Create a monitor
$monitor = Monitor::create([
    'owner_type' => \App\Models\User::class,
    'owner_id' => auth()->id(),
    'url' => 'https://example.com',
    'interval' => 10,
    'ssl_check' => true,
]);

// Check manually
php artisan monitor:check-status

// Listen to events
Event::listen(MonitorUptimeChanged::class, function ($event) {
    if ($event->status === SiteStatus::DOWN) {
        // Send alert
    }
});
```

## Support

- **GitHub Issues**: [Report bugs or request features](https://github.com/cleaniquecoders/app-pulse/issues)
- **Documentation**: You're reading it!
- **Source Code**: [View on GitHub](https://github.com/cleaniquecoders/app-pulse)

## Credits

- **Author**: [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- **License**: [MIT License](../LICENSE.md)

## Next Steps

👉 **New users**: Start with [Installation](01-getting-started/02-installation.md)

👉 **Developers**: Explore the [Architecture](02-architecture/01-overview.md)

👉 **Advanced users**: Check out [Advanced Patterns](03-usage/05-advanced-patterns.md)
