# Enums API

Complete API reference for AppPulse enums.

## Status

**Namespace:** `CleaniqueCoders\AppPulse\Enums\Status`

**Type:** `int`

**Cases:**
- `ENABLED = 1`
- `DISABLED = 0`

**Methods:**
- `label(): string`
- `description(): string`

---

## SiteStatus

**Namespace:** `CleaniqueCoders\AppPulse\Enums\SiteStatus`

**Type:** `string`

**Cases:**
- `UP = 'up'`
- `DOWN = 'down'`

**Methods:**
- `label(): string`
- `description(): string`

---

## SslStatus

**Namespace:** `CleaniqueCoders\AppPulse\Enums\SslStatus`

**Type:** `string`

**Cases:**
- `EXPIRED = 'ssl_expired'`
- `VALID = 'ssl_valid'`
- `NOT_YET_VALID = 'ssl_not_yet_valid'`
- `UNCHECKED = 'ssl_unchecked'`
- `FAILED_PARSE = 'ssl_failed_parse'`
- `FAILED_CHECK = 'ssl_failed_check'`

**Methods:**
- `label(): string`
- `description(): string`

---

## Type

**Namespace:** `CleaniqueCoders\AppPulse\Enums\Type`

**Type:** `string`

**Cases:**
- `UPTIME = 'uptime'`
- `SSL = 'ssl'`

**Methods:**
- `label(): string`
- `description(): string`
