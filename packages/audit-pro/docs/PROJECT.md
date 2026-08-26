# MTS Laravel Audit Pro - Project Documentation

## Project Overview

**Product Name:** MTS Laravel Audit Pro  
**Package Name:** magetech/laravel-audit  
**Status:** 📋 Planned  
**Version:** 1.0.0  
**License:** MIT  

---

## Package Information

| Field | Value |
|-------|-------|
| **Composer Package** | `magetech/laravel-audit` |
| **GitHub Repository** | https://github.com/magetechsol/laravel-audit |
| **Packagist URL** | https://packagist.org/packages/magetech/laravel-audit |
| **Vendor** | MageTech Solutions |
| **Website** | https://www.magetechsol.com/ |
| **PHP Version** | 8.2+ |
| **Laravel Version** | 11.x / 12.x |

---

## Installation

```bash
composer require magetech/laravel-audit
```

```bash
php artisan vendor:publish --tag=audit-config
php artisan migrate
```

---

## Quick Start

### 1. Add Auditable Trait to Model

```php
use Illuminate\Database\Eloquent\Model;
use MageTech\Audit\Contracts\Auditable;
use MageTech\Audit\Support\AuditableTrait;

class Order extends Model implements Auditable
{
    use AuditableTrait;
}
```

### 2. Manual Audit Recording

```php
use MageTech\Audit\Facades\Audit;

Audit::record()
    ->event('invoice.approved')
    ->on($invoice)
    ->by($user)
    ->withMetadata(['source' => 'admin-panel'])
    ->description('Invoice approved by admin')
    ->save();
```

### 3. Query Audit History

```php
$audits = $order->audits;
$latest = $order->getLatestAudit();
```

---

## Features Implemented

### Core Features
- [x] Model auditing with automatic event tracking
- [x] Before/after change tracking (old_values, new_values, changed_values)
- [x] Manual audit recording with fluent API
- [x] Custom event support
- [x] Actor resolution (authenticated user, API token, system)
- [x] Request context capture (IP, user agent, URL, route, etc.)

### Security Features
- [x] Field exclusion for sensitive data
- [x] Field masking with customizable strategies
- [x] Hash chaining for tamper evidence (SHA-256)
- [x] Authorization policy with configurable permissions
- [x] Rate limiting for API endpoints

### Enterprise Features
- [x] Multi-tenancy support with configurable tenant resolver
- [x] Batch operations with UUID grouping
- [x] Multi-database support (separate audit connection)
- [x] Queue support for async processing
- [x] Configurable retention policies

### API & Export
- [x] REST API endpoints for querying audits
- [x] CSV and JSON export
- [x] Optional Blade dashboard views

### Artisan Commands
- [x] `audit:install` - Install the package
- [x] `audit:verify-integrity` - Verify hash chain integrity
- [x] `audit:export` - Export audit records
- [x] `audit:cleanup` - Clean up old records
- [x] `audit:stats` - Display audit statistics
- [x] `audit:prune` - Prune records by date

---

## Configuration

The package publishes a comprehensive configuration file at `config/audit.php`:

- **Driver** - Database storage
- **Connection** - Configurable DB connection
- **Queue** - Optional async processing
- **Events** - Configurable event types
- **Exclusion** - Global field exclusions
- **Masking** - Custom masking strategies
- **Actor** - Actor resolver configuration
- **Request** - Request context capture settings
- **Tenancy** - Multi-tenant configuration
- **Integrity** - Hash chaining settings
- **Retention** - Cleanup policies
- **Permissions** - Authorization permissions

---

## Testing

```bash
vendor/bin/pest
```

---

## Dependencies

### Production
- PHP 8.2+
- illuminate/auth
- illuminate/console
- illuminate/container
- illuminate/contracts
- illuminate/database
- illuminate/events
- illuminate/http
- illuminate/support
- nesbot/carbon

### Development
- fakerphp/faker
- laravel/pint
- mockery/mockery
- nunomaduro/collision
- orchestra/testbench
- pestphp/pest
- phpstan/phpstan
- phpunit/phpunit

---

## Repository Structure

```
laravel-audit/
├── src/
│   ├── Contracts/          # 8 interfaces
│   ├── Console/            # 6 commands + 1 job
│   ├── Events/             # 4 event classes
│   ├── Exceptions/         # Custom exceptions
│   ├── Facades/            # Audit facade
│   ├── Http/               # Controllers, Middleware, Resources
│   ├── Models/             # Audit model
│   ├── Policies/           # Authorization
│   ├── Services/           # 5 service classes
│   ├── Stores/             # Database store
│   ├── Support/            # Traits, Data objects
│   ├── Transformers/       # Serializer
│   └── AuditServiceProvider.php
├── config/
│   └── audit.php
├── database/
│   └── migrations/
├── routes/
│   └── api.php
├── resources/
│   └── views/
├── tests/
│   ├── Unit/
│   ├── Feature/
│   └── Integration/
├── composer.json
├── README.md
├── CHANGELOG.md
├── SECURITY.md
├── CONTRIBUTING.md
├── LICENSE
└── phpstan.neon
```

---

## Release History

| Version | Date | Status |
|---------|------|--------|
| v1.0.0 | 2024-01-01 | ✅ Released |

---

## Support

- **Documentation:** https://github.com/magetechsol/laravel-audit
- **Issues:** https://github.com/magetechsol/laravel-audit/issues
- **Email:** info@magetechsol.com
- **Website:** https://www.magetechsol.com/

---

## License

MIT License - See [LICENSE](LICENSE) for details.

---

**Developed by [MageTech Solutions](https://www.magetechsol.com/)**
