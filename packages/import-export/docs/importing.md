# Importing

## Basic Import

```php
use MageTech\ImportExport\Support\Facades\Import;

Import::make(Product::class)
    ->from('/path/to/products.csv')
    ->map([
        'Product Name' => 'name',
        'SKU' => 'sku',
        'Price' => 'price',
    ])
    ->process();
```

## Column Mapping

Map source column names to model attributes:

```php
->map([
    'Header Name' => 'model_attribute',
    'nested.field' => 'relation.attribute', // dot notation
])
```

## Default Values

Set defaults for missing columns:

```php
->defaults([
    'is_active' => true,
    'stock_quantity' => 0,
])
```

## Skip Columns

Skip specific columns from the source:

```php
->skipColumns(['internal_id', 'notes'])
```

## Validation

```php
->validate([
    'name' => ['required', 'max:255'],
    'email' => ['required', 'email'],
    'price' => ['numeric'],
    'status' => ['in:active,inactive'],
])
```

## Data Transformation

### Type Casting

```php
->transformTypes([
    'price' => 'float',
    'is_active' => 'boolean',
    'created_at' => 'date',
])
```

### Custom Transform

```php
->transform(function (array $row) {
    $row['name'] = strtoupper($row['name']);
    $row['price'] = $row['price'] * 1.1; // Add 10% markup
    return $row;
})
```

## Duplicate Detection

```php
// Ignore duplicates (skip silently)
->duplicateDetection('ignore', 'sku')

// Reject duplicates (mark as failed)
->duplicateDetection('reject', 'sku')

// Upsert (update existing, insert new)
->duplicateDetection('upsert', 'sku')
```

## Queue Processing

```php
->queue()
->onConnection('redis')
->onQueue('imports')
->withTimeout(600)
```

## Fluent Builder

Chain all methods:

```php
Import::make(Product::class)
    ->from($file)
    ->disk('local')
    ->map([...])
    ->defaults([...])
    ->validate([...])
    ->transformTypes([...])
    ->transform(fn ($row) => $row)
    ->duplicateDetection('upsert', 'sku')
    ->chunkSize(500)
    ->queue();
```
