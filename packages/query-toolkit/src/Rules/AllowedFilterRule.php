<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedFilterRule implements ValidationRule
{
    public function __construct(
        protected array $allowedFilters,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('The filter must be an array.');

            return;
        }

        foreach (array_keys($value) as $filterName) {
            if (! in_array($filterName, $this->allowedFilters, true)) {
                $fail("The filter [{$filterName}] is not allowed.");
            }
        }
    }
}
