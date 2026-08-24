# Installation

## Requirements

- PHP 8.3+
- Laravel 13.x
- `openspout/openspout` for XLSX support

## Install via Composer

```bash
composer require magetech/laravel-import-export
```

## Publish Configuration

```bash
php artisan mts:import-export:install
```

This will:
- Publish `config/mts-import-export.php`
- Publish database migrations
- Create storage directories

## Run Migrations

```bash
php artisan migrate
```

## Configure Queue

Ensure your `.env` has a queue driver configured:

```env
QUEUE_CONNECTION=redis
```

Add the queue configuration to `config/queue.php`:

```php
'imports' => [
    'driver' => env('MTS_IMPORT_QUEUE_CONNECTION', 'redis'),
    'connection' => 'default',
    'queue' => env('MTS_IMPORT_QUEUE_NAME', 'imports'),
    'retry_after' => 90,
    'timeout' => 600,
],
```

## Start Queue Worker

```bash
php artisan queue:work --queue=imports,exports
```
