# Actions API

Complete API reference for AppPulse actions.

## CheckMonitor

**Namespace:** `CleaniqueCoders\AppPulse\Actions\CheckMonitor`

**Constructor:**
```php
new CheckMonitor(Monitor $monitor)
```

**Methods:**
- `execute(): self` - Performs uptime check

**Returns:** `self`

---

## CheckSsl

**Namespace:** `CleaniqueCoders\AppPulse\Actions\CheckSsl`

**Constructor:**
```php
new CheckSsl(Monitor $monitor, bool $force = false)
```

**Methods:**
- `execute(): self` - Performs SSL check
- `mock(SslCertificate $certificate): self` - Set mock certificate for testing

**Returns:** `self`

---

## ToggleMonitoring

**Namespace:** `CleaniqueCoders\AppPulse\Actions\ToggleMonitoring`

**Constructor:**
```php
new ToggleMonitoring(Monitor $monitor)
```

**Methods:**
- `execute(): self` - Toggles monitor status

**Returns:** `self`
