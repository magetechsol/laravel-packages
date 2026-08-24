# Upgrading Guide

## From 1.0 to 1.x

No breaking changes expected in minor versions.

## From Pre-release to 1.0

### Configuration

Publish the configuration file:

`ash
php artisan vendor:publish --provider="MageTech\QueryToolkit\QueryToolkitServiceProvider" --tag="mts-query-config"
`

### Middleware

Add the middleware to your pp/Http/Kernel.php:

`php
protected  = [
    'api' => [
        \MageTech\QueryToolkit\Middleware\ValidateQueryParameters::class,
    ],
];
`

### Service Provider

The service provider is auto-discovered. No manual registration needed.
