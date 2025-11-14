# Architecture Overview

AppPulse follows Laravel's architectural patterns and best practices, providing a clean and maintainable codebase.

## Design Principles

### Event-Driven Architecture

AppPulse uses Laravel's event system to provide extensibility. When monitor status changes occur, events are dispatched that you can listen to and handle in your application.

### Action Pattern

Business logic is encapsulated in dedicated Action classes that implement a consistent `Execute` interface. This makes the code testable and reusable.

### Queue-Based Processing

Monitor checks are dispatched to Laravel's queue system, allowing for efficient background processing and preventing blocking operations.

### Polymorphic Relationships

Monitors use polymorphic relationships to support any model as an owner, making the package flexible for various use cases.

## Core Components

### Models Layer

- **Monitor**: Represents a website or endpoint to be monitored
- **MonitorHistory**: Stores historical check results

### Business Logic Layer

- **Actions**: Encapsulated business logic (CheckMonitor, CheckSsl, ToggleMonitoring)
- **Events**: Status change notifications (MonitorUptimeChanged, SslStatusChanged)
- **Jobs**: Background processing tasks (CheckMonitorJob, CheckSslJob)

### Enums

- **Status**: Monitoring enabled/disabled state
- **SiteStatus**: UP/DOWN status for websites
- **SslStatus**: SSL certificate validity status
- **Type**: UPTIME/SSL check types

### Infrastructure Layer

- **Commands**: CLI interface for manual operations
- **Service Provider**: Package registration and bootstrapping
- **Configuration**: Centralized settings management

## Data Flow

```
┌─────────────────┐
│   Scheduler     │
│  (Every X min)  │
└────────┬────────┘
         │
         ▼
┌─────────────────────┐
│ CheckMonitorCommand │
└────────┬────────────┘
         │
         ├─────────────────┐
         ▼                 ▼
┌──────────────┐   ┌──────────────┐
│CheckMonitorJob│   │  CheckSslJob │
└──────┬────────┘   └──────┬───────┘
       │                   │
       ▼                   ▼
┌──────────────┐   ┌──────────────┐
│ CheckMonitor │   │   CheckSsl   │
│   (Action)   │   │   (Action)   │
└──────┬────────┘   └──────┬───────┘
       │                   │
       ▼                   ▼
┌──────────────────────────────┐
│      MonitorHistory          │
│   (Database Record)          │
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│   Events Dispatched          │
│ - MonitorUptimeChanged       │
│ - SslStatusChanged           │
└──────────────────────────────┘
```

## Request Lifecycle

1. **Scheduled Trigger**: Laravel scheduler runs at configured intervals
2. **Command Execution**: `CheckMonitorStatusCommand` fetches active monitors
3. **Job Dispatch**: Monitors are chunked and dispatched to the queue
4. **Action Execution**: Jobs execute corresponding actions
5. **Data Persistence**: Results stored in `MonitorHistory`
6. **Event Broadcasting**: Status change events dispatched
7. **Custom Handlers**: Your application can listen and respond to events

## Database Schema

### monitors table

- Stores monitor configurations
- Polymorphic relationship to any owner model
- Tracks last check time and status

### monitor_histories table

- Stores all check results
- Links to monitors via foreign key
- Records response times and error messages
- Partitioned by check type (UPTIME, SSL)

## Extension Points

AppPulse provides several extension points:

1. **Event Listeners**: Listen to status change events
2. **Custom Actions**: Implement additional monitoring actions
3. **Queue Configuration**: Use dedicated queues for priority processing
4. **Owner Models**: Monitor any resource in your application

## Dependencies

### Required Packages

- `cleaniquecoders/traitify`: UUID and enum helper traits
- `spatie/ssl-certificate`: SSL certificate validation
- `spatie/laravel-package-tools`: Package scaffolding

### Laravel Components

- Eloquent ORM
- Queue System
- Event System
- Scheduler
- HTTP Client

## Performance Considerations

### Chunking

Monitors are processed in configurable chunks to prevent memory exhaustion and ensure efficient batch processing.

### Queue Workers

Running multiple queue workers in parallel improves throughput for high-volume monitoring.

### Database Indexes

Key fields like `monitor_id`, `type`, and `created_at` are indexed for optimal query performance.

### Caching

Consider implementing caching strategies for frequently accessed monitor data in your application.
