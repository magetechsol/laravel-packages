<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Security;

use Illuminate\Config\Repository;

class PiiMasker
{
    protected array $patterns = [
        'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
        'phone' => '/\b(\+?1?[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}\b/',
        'ssn' => '/\b\d{3}[-]?\d{2}[-]?\d{4}\b/',
        'credit_card' => '/\b\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}\b/',
        'ip_address' => '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/',
        'url_with_credentials' => '/https?:\/\/[^:]+:[^@]+@[^\/]+/',
    ];

    public function __construct(
        protected Repository $config,
    ) {}

    public function mask(string $value): string
    {
        if (! $this->config->get('mts-ai.audit.mask_pii', true)) {
            return $value;
        }

        $masked = $value;

        foreach ($this->patterns as $type => $pattern) {
            $masked = preg_replace_callback($pattern, function ($matches) use ($type) {
                return $this->maskMatch($matches[0], $type);
            }, $masked);
        }

        $sensitiveFields = $this->config->get('mts-ai.audit.sensitive_fields', []);

        foreach ($sensitiveFields as $field) {
            $masked = $this->maskSensitiveKey($masked, $field);
        }

        return $masked;
    }

    protected function maskMatch(string $value, string $type): string
    {
        return match ($type) {
            'email' => $this->maskEmail($value),
            'phone' => $this->maskPhone($value),
            'ssn' => '***-**-' . substr($value, -4),
            'credit_card' => '****-****-****-' . substr($value, -4),
            'ip_address' => $this->maskIp($value),
            'url_with_credentials' => preg_replace('/\/\/[^:]+:[^@]+@/', '//***:***@', $value),
            default => str_repeat('*', strlen($value)),
        };
    }

    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);

        if (count($parts) !== 2) {
            return str_repeat('*', strlen($email));
        }

        $name = $parts[0];
        $domain = $parts[1];

        $maskedName = strlen($name) > 2
            ? $name[0] . str_repeat('*', strlen($name) - 2) . $name[strlen($name) - 1]
            : str_repeat('*', strlen($name));

        return "{$maskedName}@{$domain}";
    }

    protected function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) >= 4) {
            return str_repeat('*', strlen($phone) - 4) . substr($phone, -4);
        }

        return str_repeat('*', strlen($phone));
    }

    protected function maskIp(string $ip): string
    {
        $parts = explode('.', $ip);

        if (count($parts) === 4) {
            return $parts[0] . '.' . $parts[1] . '.*.*';
        }

        return str_repeat('*', strlen($ip));
    }

    protected function maskSensitiveKey(string $text, string $key): string
    {
        $patterns = [
            "/{$key}\s*[:=]\s*['\"]?([^'\"\s,;]+)['\"]?/i",
        ];

        foreach ($patterns as $pattern) {
            $text = preg_replace_callback($pattern, function ($matches) use ($key) {
                $value = $matches[1];
                $masked = strlen($value) > 4
                    ? substr($value, 0, 2) . str_repeat('*', strlen($value) - 4) . substr($value, -2)
                    : str_repeat('*', strlen($value));

                return "{$key}: {$masked}";
            }, $text);
        }

        return $text;
    }

    public function addPattern(string $type, string $pattern): void
    {
        $this->patterns[$type] = $pattern;
    }

    public function hasPattern(string $type): bool
    {
        return isset($this->patterns[$type]);
    }

    public function removePattern(string $type): void
    {
        unset($this->patterns[$type]);
    }
}
