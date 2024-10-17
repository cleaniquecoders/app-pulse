[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/app-pulse.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/app-pulse) [![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/app-pulse/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/app-pulse/actions?query=workflow%3Arun-tests+branch%3Amain) [![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/app-pulse/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/app-pulse/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain) [![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/app-pulse.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/app-pulse)

# **AppPulse**

A comprehensive, easy-to-use monitoring tool with **uptime tracking, SSL certificate checks**, and **customizable notifications** designed for Laravel applications.

**AppPulse** allows developers to monitor websites efficiently by:

- Tracking **website uptime** and logging response times.
- **Validating SSL certificates** and sending alerts when expiry is near.
- Providing **custom notifications** that adhere to user configurations, ensuring personalized alerts.

---

## **Installation**

You can install the package via Composer:

```bash
composer require cleaniquecoders/app-pulse
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="app-pulse-migrations"
php artisan migrate
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="app-pulse-config"
```

Published config file contents:

```php
return [
    'default_check_interval' => 5, // Default interval in minutes
    'ssl_warning_days' => 30, // Days before SSL expiry to trigger alert
];
```

Optionally, you can publish the views with:

```bash
php artisan vendor:publish --tag="app-pulse-views"
```

---

## **Usage Example**

### 1. **Adding Monitors**

Use the following logic to create a new monitor record in your application:

```php
use CleaniqueCoders\AppPulse\Models\Monitor;

$monitor = Monitor::create([
    'owner_type' => \App\Models\User::class, // Owner model type
    'owner_id' => 1, // Owner ID (e.g., User or Application)
    'url' => 'https://example.com', // URL to monitor
    'interval' => 10, // Interval (in minutes) between checks
    'ssl_check' => true, // Enable or disable SSL check
]);
```

---

### 2. **Running Checks Manually**

You can trigger all monitor checks manually with the following command:

```bash
php artisan monitor:check-status
```

---

### 3. **Automated Checks with Scheduler**

1. Ensure Laravel’s **scheduler** is configured to run every minute on your server:

   ```bash
   * * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
   ```

2. The **AppPulse scheduler** will run every X minutes, as defined in the `config/app-pulse.php`:

   ```php
   'scheduler' => [
       'interval' => env('APP_PULSE_SCHEDULER_INTERVAL', 10), // Run every 10 minutes
       'queue' => env('APP_PULSE_SCHEDULER_QUEUE', 'default'), // Queue to use
       'chunk' => env('APP_PULSE_SCHEDULER_CHUNK', 100), // Process monitors in batches
   ],
   ```

3. When the scheduler runs, it will:
   - Dispatch a **`CheckMonitorJob`** to check uptime.
   - Dispatch a **`CheckSslJob`** if SSL monitoring is enabled.

---

### 4. **Handling Events**

Developers can extend **AppPulse** by listening to the following events:

- **`MonitorUptimeChanged`**: Fired when a monitor’s uptime status changes.
- **`SslStatusChanged`**: Fired when a monitor's SSL status changes.

**Example Event Listener Registration** (in `EventServiceProvider`):

```php
protected $listen = [
    \CleaniqueCoders\AppPulse\Events\MonitorUptimeChanged::class => [
        \App\Listeners\HandleUptimeChange::class,
    ],
    \CleaniqueCoders\AppPulse\Events\SslStatusChanged::class => [
        \App\Listeners\HandleSslStatusChange::class,
    ],
];
```

---

## **Testing**

Run the tests with:

```bash
composer test
```

---

## **Changelog**

See [CHANGELOG](CHANGELOG.md) for recent changes.

---

## **Contributing**

Please see [CONTRIBUTING](CONTRIBUTING.md) for details on contributing.

---

## **Security Vulnerabilities**

For security concerns, review our [security policy](../../security/policy).

---

## **Credits**

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

---

## **License**

This package is open-sourced software licensed under the [MIT License](LICENSE.md).
