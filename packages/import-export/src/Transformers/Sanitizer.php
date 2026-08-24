<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Transformers;

final readonly class Sanitizer
{
    private const FORMULA_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    public function __construct(
        private bool $formulaProtection = true,
    ) {
    }

    public function sanitize(string $value): string
    {
        $value = trim($value);

        if ($this->formulaProtection && $this->isFormulaInjection($value)) {
            $value = "'".$value;
        }

        return $value;
    }

    public function sanitizeArray(array $row): array
    {
        return array_map(
            fn ($value) => is_string($value) ? $this->sanitize($value) : $value,
            $row,
        );
    }

    public function isFormulaInjection(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        foreach (self::FORMULA_PREFIXES as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public static function sanitizeValue(string $value): string
    {
        return (new self)->sanitize($value);
    }

    public static function sanitizeRow(array $row): array
    {
        return (new self)->sanitizeArray($row);
    }
}
