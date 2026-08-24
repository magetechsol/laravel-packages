# Queue Setup

## Supported Drivers

- Redis (recommended)
- Database
- SQS-compatible queues

## Configuration

Add to `config/queue.php`:

```php
'imports' => [
    'driver' => env('MTS_IMPORT_QUEUE_CONNECTION', 'redis'),
    'connection' => 'default',
    'queue' => env('MTS_IMPORT_QUEUE_NAME', 'imports'),
    'retry_after' => 90,
    'timeout' => 600,
    'tries' => 3,
    'max_exceptions' => 10,
],

'exports' => [
    'driver' => env('MTS_EXPORT_QUEUE_CONNECTION', 'redis'),
    'connection' => 'default',
    'queue' => env('MTS_EXPORT_QUEUE_NAME', 'exports'),
    'retry_after' => 90,
    'timeout' => 600,
    'tries' => 3,
],
```

## Environment Variables

```env
QUEUE_CONNECTION=redis
MTS_IMPORT_QUEUE_CONNECTION=redis
MTS_IMPORT_QUEUE_NAME=imports
MTS_EXPORT_QUEUE_CONNECTION=redis
MTS_EXPORT_QUEUE_NAME=exports
```

## Running Workers

```bash
php artisan queue:work --queue=imports,exports
```

## Supervisor Configuration

```ini
[program:laravel-queue-worker]
command=php /path/to/artisan queue:work --queue=imports,exports --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
```

## Scaling

For high-volume imports:
1. Increase `chunk_size` in config
2. Run multiple queue workers
3. Use Redis for better performance
4. Monitor queue length with `php artisan queue:size`
