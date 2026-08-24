# Error Handling

## Error Report Generation

Generate downloadable CSV/XLSX error reports:

```php
use MageTech\ImportExport\ErrorReport;

$report = new ErrorReport();
$path = $report->generate($import);
```

## Error Report Format

| Column | Description |
|---|---|
| row_number | The row number in the source file |
| column | The column that caused the error |
| value | The value that failed validation |
| error | The error message |
| error_code | Optional error code |

## Get Errors as Array

```php
$errors = $report->getErrorsAsArray($import);

// Returns:
// [
//     ['row_number' => 2, 'column' => 'email', 'value' => 'invalid', 'error' => 'Email is invalid', 'error_code' => null],
// ]
```

## Retry Failed Rows

```bash
php artisan mts:import:retry {id}
```

Or programmatically:

```php
use MageTech\ImportExport\Jobs\RetryImportJob;

RetryImportJob::dispatch($import);
```

## Event Listeners

```php
use MageTech\ImportExport\Events\ImportFailed;

Event::listen(ImportFailed::class, function ($event) {
    Log::error("Import {$event->import->id} failed", [
        'exception' => $event->exception?->getMessage(),
    ]);
});
```

## Logging

Enable error logging in config:

```php
'error_handling' => [
    'log_errors' => true,
    'max_errors_per_import' => 1000,
],
```
