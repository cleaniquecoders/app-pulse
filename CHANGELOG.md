# Changelog

All notable changes to `app-pulse` will be documented in this file.

## v1.0.0 - 2024-10-17

### **Release Notes for AppPulse v1.0.0**

#### **Overview**

The first stable release of **AppPulse** (v1.0.0) introduces a robust and flexible monitoring tool for Laravel applications.

It provides core features for tracking **uptime status** and **SSL certificate validity**, with an event-driven design for extensibility.


---

#### **Key Features**

1. **Uptime Monitoring**
   
   - Track the status of monitored URLs with automatic response logging.
   - Support for scheduled checks at customizable intervals (default: every 10 minutes).
   
2. **SSL Certificate Validation**
   
   - Monitor the SSL status of websites, including expiry detection.
   - Configurable SSL alert window for upcoming expirations (default: 30 days).
   
3. **Event-Driven Notifications**
   
   - Custom events (`MonitorUptimeChanged` and `SslStatusChanged`) allow developers to hook into changes and trigger their own listeners.
   
4. **Queue and Job Handling**
   
   - Background jobs for uptime and SSL checks ensure smooth, non-blocking operations.
   - Configurable queue and batch chunk sizes for optimized performance.
   
5. **Scheduler Integration**
   
   - Easily configurable with Laravel’s scheduler to automate checks.
   - Developers need to set up a **cron job** to ensure continuous monitoring.
   


---

#### **Installation Improvements**

- **Simple installation via Composer**:
  
  ```bash
  composer require cleaniquecoders/app-pulse
  
  ```
- Publish configuration and migrations with:
  
  ```bash
  php artisan vendor:publish --tag="app-pulse-config"
  php artisan vendor:publish --tag="app-pulse-migrations"
  php artisan migrate
  
  ```
- Cron setup instructions included for smooth operation with Laravel’s scheduler.
  


---

#### **Bug Fixes & Optimizations**

- Fixed parsing issues with SSL certificate expiration dates.
- Optimized chunk-based processing for large-scale monitoring.
- Enhanced test coverage to ensure stability and reliability.


---

#### **Breaking Changes**

- Initial release, no backward compatibility concerns.


---

#### **Upgrade Instructions**

No upgrade steps are necessary, as this is the **first stable release**.


---

#### **Known Issues**

- None reported at the time of release. Developers are encouraged to report issues via GitHub.


---

#### **Contributors**

- **Nasrul Hazim Bin Mohamad** – Lead Developer
- And [all contributors](../../contributors) who supported the development of this package.


---

#### **Changelog**

See the [CHANGELOG](CHANGELOG.md) for more detailed version changes.


---

Enjoy using **AppPulse**! 🚀

**Full Changelog**: [https://github.com/cleaniquecoders/app-pulse/commits/v1.0.0](https://github.com/cleaniquecoders/app-pulse/commits/v1.0.0)
