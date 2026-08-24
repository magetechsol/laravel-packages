<?php

declare(strict_types=1);
use Illuminate\Database\Eloquent\Model;
use MageTech\ImportExport\Export;
use MageTech\ImportExport\Import;
use MageTech\ImportExport\Transformers\Sanitizer;

if (! function_exists('import')) {
    /**
     * Create a new Import instance.
     *
     * @param  class-string<Model>  $modelClass
     */
    function import(string $modelClass): Import
    {
        return Import::make($modelClass);
    }
}

if (! function_exists('export')) {
    /**
     * Create a new Export instance.
     *
     * @param  class-string<Model>  $modelClass
     */
    function export(string $modelClass): Export
    {
        return Export::make($modelClass);
    }
}

if (! function_exists('sanitize_formula')) {
    /**
     * Sanitize a value to prevent CSV formula injection.
     */
    function sanitize_formula(string $value): string
    {
        return Sanitizer::sanitizeValue($value);
    }
}
