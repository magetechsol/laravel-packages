# MTS Laravel DevTools

Local-only Laravel developer dashboard and diagnostic toolkit — application info, performance metrics, security audit, package status, and artisan commands.

## Features

- **Web Dashboard** — Tailwind-powered dashboard at `/devtools` with 4 sections
- **Application Info** — Laravel version, PHP version, environment, database, cache, queue
- **Performance Metrics** — Requests, queries, slow queries, jobs, cache stats
- **Security Audit** — Debug mode, config status, routes, HTTPS, PHP extensions
- **Package Status** — Installed packages, versions, outdated packages
- **Health Checks** — Real-time health status with warnings and critical alerts
- **Artisan Commands** — 6 CLI commands for diagnostics
- **Production Safe** — Auto-disabled in production, IP whitelist, optional password protection

## Requirements

- PHP 8.2+
- Laravel 11.x, 12.x, or 13.x

## Installation

```bash
composer require magetech/laravel-devtools
php artisan mts:devtools:install
```

## Configuration

Add to your `.env`:

```env
# Enable DevTools (defaults to true in non-production)
MTS_DEVTOOLS_ENABLED=true

# Dashboard URL prefix (default: devtools)
MTS_DEVTOOLS_PREFIX=devtools

# IP whitelist (default: 127.0.0.1,::1)
MTS_DEVTOOLS_ALLOWED_IPS=127.0.0.1,::1

# Optional password protection
MTS_DEVTOOLS_PASSWORD=your-secret-password

# Enable/disable web dashboard
MTS_DEVTOOLS_DASHBOARD=true

# Enable/disable artisan commands
MTS_DEVTOOLS_COMMANDS=true

# Toggle individual collectors
MTS_DEVTOOLS_COLLECT_APPLICATION=true
MTS_DEVTOOLS_COLLECT_PERFORMANCE=true
MTS_DEVTOOLS_COLLECT_SECURITY=true
MTS_DEVTOOLS_COLLECT_PACKAGES=true

# Auto-refresh interval in seconds (0 to disable)
MTS_DEVTOOLS_REFRESH_INTERVAL=30

# Slow query threshold in milliseconds
MTS_DEVTOOLS_SLOW_QUERY_THRESHOLD=1000
```

Publish the config file:

```bash
php artisan vendor:publish --tag=mts-devtools-config
```

## Web Dashboard

Visit `/devtools` in your browser. The dashboard includes 4 tabs:

### Application
- Laravel version
- PHP version
- Environment
- Database driver and version
- Cache driver and stores
- Queue driver and connections

### Performance
- Request count
- Query count
- Slow queries (from log files)
- Queued jobs
- Failed jobs
- Cache hit/miss ratios

### Security
- Debug mode status
- Environment status
- Configuration cache status
- Route count by method
- HTTPS status
- PHP extensions status

### Packages
- Installed packages with versions
- Dev vs production packages
- Outdated packages with upgrade info

## Artisan Commands

### Full Diagnostic
```bash
php artisan mts:doctor
```
Runs all checks and displays a comprehensive report with overall health status.

### Health Status
```bash
php artisan mts:health
```
Shows health checks for environment, debug mode, database, slow queries, and failed jobs.

### Performance Metrics
```bash
php artisan mts:performance
php artisan mts:performance --slow-only
```
Displays performance metrics including requests, queries, jobs, and cache stats.

### Security Audit
```bash
php artisan mts:security
```
Shows debug mode, environment, config cache, routes, HTTPS, and PHP extensions.

### Route Listing
```bash
php artisan mts:routes
php artisan mts:routes --method=GET
php artisan mts:routes --name=api
```
Lists all routes with methods, URIs, names, actions, and middleware.

### Dependencies
```bash
php artisan mts:dependencies
php artisan mts:dependencies --outdated
```
Shows installed packages with versions and outdated packages with upgrade targets.

## Production Security

The package includes multiple security layers:

1. **Auto-disable** — Automatically disabled when `APP_ENV=production`
2. **IP Whitelist** — Only accessible from `127.0.0.1` / `::1` by default
3. **Password Protection** — Optional config-based password for dashboard
4. **Middleware Guard** — Dashboard routes protected by access middleware
5. **Command Guards** — Artisan commands check config before executing

### Production `.env` Example

```env
MTS_DEVTOOLS_ENABLED=false
MTS_DEVTOOLS_PROD_AUTO_DISABLE=true
MTS_DEVTOOLS_PROD_REQUIRE_PASSWORD=true
```

## Helper Functions

```php
// Get the DevTools instance
$devtools = devtools();

// Check if DevTools is enabled
if (devtools_enabled()) {
    // ...
}

// Get all data
$data = devtools()->getAllData();

// Get health status
$health = devtools()->getHealthStatus();
$overall = devtools()->getOverallHealth();
```

## Facade

```php
use MageTech\DevTools\Support\Facades\DevToolsFacade;

DevToolsFacade::isEnabled();
DevToolsFacade::getApplicationData();
DevToolsFacade::getPerformanceData();
DevToolsFacade::getSecurityData();
DevToolsFacade::getPackageData();
DevToolsFacade::getAllData();
DevToolsFacade::getHealthStatus();
DevToolsFacade::getOverallHealth();
```

## Testing

```bash
composer test
```

## License

MIT License. See [LICENSE](LICENSE) for details.
