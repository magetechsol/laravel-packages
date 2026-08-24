# Configuration

The configuration file is published to `config/mts-import-export.php`.

## Upload

```php
'upload' => [
    'max_file_size' => 10240, // KB
    'allowed_extensions' => ['csv', 'xlsx', 'json', 'xml'],
    'allowed_mime_types' => [...],
],
```

## Import

```php
'import' => [
    'chunk_size' => 1000,        // Rows per chunk
    'batch_size' => 500,         // Rows per DB batch
    'queue_connection' => 'redis',
    'queue_name' => 'imports',
    'timeout' => 600,            // seconds
    'tries' => 3,
],
```

## Export

```php
'export' => [
    'chunk_size' => 1000,
    'queue_connection' => 'redis',
    'queue_name' => 'exports',
    'timeout' => 600,
],
```

## Validation

```php
'validation' => [
    'require_header' => true,
    'max_rows' => null,
    'stop_on_failure' => false,
    'duplicate_detection' => 'ignore', // ignore | reject | upsert
],
```

## Security

```php
'security' => [
    'sanitize_filenames' => true,
    'formula_injection_protection' => true,
    'max_file_size' => 10240,
    'validate_mime_real' => true,
    'prevent_path_traversal' => true,
],
```

## Error Handling

```php
'error_handling' => [
    'log_errors' => true,
    'max_errors_per_import' => 1000,
    'generate_error_report' => true,
    'error_report_format' => 'csv', // csv | xlsx
],
```
