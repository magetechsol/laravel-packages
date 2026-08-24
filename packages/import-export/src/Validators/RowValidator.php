<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Validators;

final class RowValidator
{
    private array $rules = [];

    private array $errors = [];

    public function __construct(
        array $rules = [],
    ) {
        $this->rules = $rules;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Validate a single row.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, list<string>>
     */
    public function validate(array $row): array
    {
        $this->errors = [];

        foreach ($this->rules as $column => $rules) {
            $value = $row[$column] ?? null;
            $columnErrors = $this->validateColumn($column, $value, $rules);

            if ($columnErrors !== []) {
                $this->errors[$column] = $columnErrors;
            }
        }

        return $this->errors;
    }

    public function isValid(array $row): bool
    {
        $errors = $this->validate($row);

        return $errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @param  list<string>  $rules
     * @return list<string>
     */
    private function validateColumn(string $column, mixed $value, array $rules): array
    {
        $errors = [];

        foreach ($rules as $rule) {
            $ruleStr = is_string($rule) ? $rule : '';

            if ($this->isRequired($ruleStr) && ($value === null || $value === '')) {
                $errors[] = "{$column} is required.";

                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            if ($this->isEmail($ruleStr) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "{$column} must be a valid email address.";
            }

            if ($this->isNumeric($ruleStr) && ! is_numeric($value)) {
                $errors[] = "{$column} must be numeric.";
            }

            if ($this->isBoolean($ruleStr) && ! in_array(strtolower((string) $value), ['true', 'false', '1', '0', 'yes', 'no'], true)) {
                $errors[] = "{$column} must be a boolean value.";
            }

            if ($this->isDate($ruleStr) && strtotime((string) $value) === false) {
                $errors[] = "{$column} must be a valid date.";
            }

            if (preg_match('/^max:(\d+)$/', $ruleStr, $matches)) {
                $max = (int) $matches[1];

                if (strlen((string) $value) > $max) {
                    $errors[] = "{$column} must not exceed {$max} characters.";
                }
            }

            if (preg_match('/^min:(\d+)$/', $ruleStr, $matches)) {
                $min = (int) $matches[1];

                if (strlen((string) $value) < $min) {
                    $errors[] = "{$column} must be at least {$min} characters.";
                }
            }

            if (preg_match('/^in:(.+)$/', $ruleStr, $matches)) {
                $allowed = explode(',', $matches[1]);

                if (! in_array((string) $value, $allowed, true)) {
                    $errors[] = "{$column} must be one of: ".implode(', ', $allowed).'.';
                }
            }
        }

        return $errors;
    }

    private function isRequired(string $rule): bool
    {
        return $rule === 'required';
    }

    private function isEmail(string $rule): bool
    {
        return $rule === 'email';
    }

    private function isNumeric(string $rule): bool
    {
        return $rule === 'numeric';
    }

    private function isBoolean(string $rule): bool
    {
        return $rule === 'boolean';
    }

    private function isDate(string $rule): bool
    {
        return $rule === 'date';
    }
}
