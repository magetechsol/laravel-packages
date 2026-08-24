# Exporting

## Basic Export

```php
use MageTech\ImportExport\Support\Facades\Export;

Export::make(Product::class)
    ->to('products.csv')
    ->process();
```

## Export with Columns

```php
Export::make(Product::class)
    ->to('products.xlsx')
    ->columns(['name', 'sku', 'price'])
    ->process();
```

## Export with Filters

```php
Export::make(Product::class)
    ->to('active_products.csv')
    ->filter(fn ($query) => $query->where('is_active', true))
    ->process();
```

## Export Formats

```php
// CSV
->to('export.csv')

// XLSX
->to('export.xlsx')

// JSON
->to('export.json')

// XML
->to('export.xml')
```

## Queue Export

```php
Export::make(Product::class)
    ->to('products.xlsx')
    ->columns(['name', 'sku', 'price'])
    ->queue()
    ->onQueue('exports');
```

## Artisan Export

```bash
php artisan mts:export App\Models\Product --format=csv --columns=name,sku,price
php artisan mts:export App\Models\Product --format=xlsx --queue
```
