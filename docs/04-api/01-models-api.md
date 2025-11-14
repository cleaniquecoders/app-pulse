# Models API

Complete API reference for AppPulse models. See [Architecture - Models](../02-architecture/02-models.md) for detailed examples.

## Monitor

**Namespace:** `CleaniqueCoders\AppPulse\Models\Monitor`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `id` | int | Primary key |
| `uuid` | string | Unique identifier |
| `owner_id` | int | Owner model ID |
| `owner_type` | string | Owner model class name |
| `url` | string | URL to monitor |
| `status` | bool | Monitoring enabled/disabled |
| `interval` | int | Check interval in minutes |
| `ssl_check` | bool | SSL checking enabled |
| `last_checked_at` | Carbon\|null | Last check timestamp |

### Relationships

- `owner(): MorphTo` - Polymorphic owner relationship
- `histories(): HasMany` - Monitor history records

### Methods

- `hasHistory(Type $type): bool` - Check if history exists
- `getLatestHistory(Type $type): MonitorHistory` - Get latest history

---

## MonitorHistory

**Namespace:** `CleaniqueCoders\AppPulse\Models\MonitorHistory`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `id` | int | Primary key |
| `uuid` | string | Unique identifier |
| `monitor_id` | int | Foreign key to monitors |
| `type` | string | Check type (uptime/ssl) |
| `status` | string | Check result status |
| `response_time` | float\|null | Response time (ms) or days to expiry |
| `error_message` | string\|null | Error message if failed |

### Relationships

- `monitor(): BelongsTo` - Parent monitor record
