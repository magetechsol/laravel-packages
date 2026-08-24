<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedSortRule implements ValidationRule
{
    public function __construct(
        protected array $allowedSorts,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The sort parameter must be a string.');

            return;
        }

        $sorts = array_map('trim', explode(',', $value));

        foreach ($sorts as $sort) {
            $field = ltrim($sort, '-');

            if (! in_array($field, $this->allowedSorts, true)) {
                $fail("The sort field [{$field}] is not allowed.");
            }
        }
    }
}
