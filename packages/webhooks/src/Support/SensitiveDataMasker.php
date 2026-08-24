<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Support;

final class SensitiveDataMasker
{
    private array $sensitiveFields;

    public function __construct()
    {
        $this->sensitiveFields = array_map(
            'strtolower',
            config('mts-webhooks.security.mask_sensitive_fields', []),
        );
    }

    public function mask(array $data): array
    {
        $masked = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $masked[$key] = $this->mask($value);
            } elseif (is_string($value) && $this->isSensitiveField($key)) {
                $masked[$key] = $this->maskValue($value);
            } else {
                $masked[$key] = $value;
            }
        }

        return $masked;
    }

    private function isSensitiveField(string $field): bool
    {
        return in_array(strtolower($field), $this->sensitiveFields, true);
    }

    private function maskValue(string $value): string
    {
        $length = strlen($value);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        $visibleChars = min(4, (int) ceil($length * 0.2));
        $maskedPart = str_repeat('*', $length - $visibleChars);

        return substr($value, 0, $visibleChars) . $maskedPart;
    }
}
