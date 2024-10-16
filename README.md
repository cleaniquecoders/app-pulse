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

You can add monitors through your application logic:

```php
use CleaniqueCoders\AppPulse\Models\Monitor;

$monitor = Monitor::create([
    'owner_type' => \App\Models\User::class,
    'owner_id' => 1, // User or Application ID
    'url' => 'https://example.com',
    'interval' => 5,
    'ssl_check' => true,
]);
```

To initiate a check for all monitors:

```bash
php artisan apppulse:check
```

---

## **Notifications**

**Notifications** will be sent to the owner of the monitor based on the configured notification channels (e.g., Email, Slack). You can customize the notification settings per user.

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

