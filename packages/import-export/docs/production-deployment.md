# Production Deployment

## Server Requirements

- PHP 8.3+ with extensions: pdo, pdo_sqlite, mbstring, xml, finfo
- Redis (recommended for queues)
- Adequate disk space for imports/exports

## Optimization

### Queue Configuration

```env
QUEUE_CONNECTION=redis
MTS_IMPORT_CHUNK_SIZE=1000
MTS_IMPORT_BATCH_SIZE=500
```

### Database

```env
DB_CONNECTION=mysql
```

### Cache

```env
CACHE_DRIVER=redis
```

## Monitoring

### Queue Monitoring

```bash
php artisan queue:size imports
```

### Failed Jobs

```bash
php artisan queue:failed
php artisan queue:retry {id}
```

### Import Status

```php
use MageTech\ImportExport\Models\Import;

$pending = Import::status('pending')->count();
$processing = Import::status('processing')->count();
$failed = Import::status('failed')->count();
```

## Scaling

### Horizontal Scaling

1. Run multiple queue workers
2. Use Redis for shared queue
3. Use shared storage (S3, NFS)

### Vertical Scaling

1. Increase `chunk_size` for faster processing
2. Increase `batch_size` for fewer DB queries
3. Increase `timeout` for large files

## Backup

Regularly backup:
- `storage/app/imports/`
- `storage/app/exports/`
- `storage/app/error_reports/`
- Database tables: `imports`, `import_rows`, `import_errors`, `exports`

## Health Check

```php
Route::get('/health', function () {
    $pendingImports = \MageTech\ImportExport\Models\Import::status('pending')->count();
    $failedImports = \MageTech\ImportExport\Models\Import::status('failed')->count();

    return response()->json([
        'status' => 'ok',
        'pending_imports' => $pendingImports,
        'failed_imports' => $failedImports,
    ]);
});
```
