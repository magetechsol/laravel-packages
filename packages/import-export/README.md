# MTS Laravel Import Export

Enterprise import/export engine for Laravel with streaming, queue processing, and progress tracking.

## Features

- **Multi-format support**: CSV, XLSX, JSON, XML
- **Streaming readers**: Never loads entire files into memory
- **Queue processing**: Redis, Database, SQS-compatible queues
- **Column mapping**: Map source columns to model attributes
- **Data transformation**: Type casting, custom transforms, sanitization
- **Validation**: Row-level and file-level validation
- **Duplicate detection**: Ignore, reject, or upsert modes
- **Progress tracking**: Real-time progress with events
- **Error reporting**: Downloadable CSV/XLSX error reports
- **Security**: Formula injection protection, MIME validation, path traversal prevention
- **Cancellation**: Cancel running imports
- **Retry**: Retry failed rows

## Requirements

- PHP 8.3+
- Laravel 13.x

## Installation

```bash
composer require magetech/laravel-import-export
php artisan mts:import-export:install
php artisan migrate
```

## Quick Start

### Import

```php
use MageTech\ImportExport\Support\Facades\Import;

Import::make(Product::class)
    ->from($file)
    ->map([
        'Product Name' => 'name',
        'SKU' => 'sku',
        'Price' => 'price',
    ])
    ->validate([
        'name' => ['required'],
        'price' => ['numeric'],
    ])
    ->queue();
```

### Export

```php
use MageTech\ImportExport\Support\Facades\Export;

Export::make(Product::class)
    ->to('products.xlsx')
    ->columns(['name', 'sku', 'price'])
    ->filter(fn ($query) => $query->where('active', true))
    ->process();
```

## Artisan Commands

```bash
php artisan mts:import-export:install     # Install package
php artisan mts:import:process            # Process pending imports
php artisan mts:import:retry {id}         # Retry failed rows
php artisan mts:import:cancel {id}        # Cancel a running import
php artisan mts:export {model}            # Export a model
php artisan mts:make:import {name}        # Scaffold an Import class
```

## Queue Setup

Add to your `config/queue.php`:

```php
'imports' => [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => env('MTS_IMPORT_QUEUE', 'imports'),
    'retry_after' => 90,
    'timeout' => 600,
],
```

Run the queue worker:

```bash
php artisan queue:work --queue=imports,exports
```

## Events

```php
use MageTech\ImportExport\Events\ImportStarted;
use MageTech\ImportExport\Events\ImportProgress;
use MageTech\ImportExport\Events\ImportCompleted;
use MageTech\ImportExport\Events\ImportFailed;
use MageTech\ImportExport\Events\ImportCancelled;
```

## Error Handling

```php
use MageTech\ImportExport\ErrorReport;

$report = new ErrorReport();
$path = $report->generate($import);
$errors = $report->getErrorsAsArray($import);
```

## Security

- **Formula injection protection**: Prefixes cells starting with `=`, `+`, `-`, `@`
- **MIME validation**: Uses `finfo` to verify file types
- **Path traversal prevention**: Validates file paths with `realpath()`
- **Filename sanitization**: Strips dangerous characters from filenames
- **File size limits**: Configurable maximum upload size

## License

MIT License. See [LICENSE](LICENSE) for details.
