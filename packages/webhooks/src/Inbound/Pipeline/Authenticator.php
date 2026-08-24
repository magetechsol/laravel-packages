<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Pipeline;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MageTech\Webhooks\Contracts\WebhookProviderContract;
use MageTech\Webhooks\Exceptions\ReplayAttackException;
use MageTech\Webhooks\Exceptions\SignatureVerificationException;
use MageTech\Webhooks\Inbound\Providers\GenericProvider;
use MageTech\Webhooks\Inbound\Providers\MagentoProvider;
use MageTech\Webhooks\Inbound\Providers\RazorpayProvider;
use MageTech\Webhooks\Inbound\Providers\ShopifyProvider;
use MageTech\Webhooks\Inbound\Providers\StripeProvider;

class Authenticator
{
    private const PROVIDER_MAP = [
        'stripe' => StripeProvider::class,
        'razorpay' => RazorpayProvider::class,
        'shopify' => ShopifyProvider::class,
        'magento' => MagentoProvider::class,
        'generic' => GenericProvider::class,
    ];

    public function authenticate(Request $request, string $provider): WebhookProviderContract
    {
        $providerInstance = $this->resolveProvider($provider);

        if (config('mts-webhooks.security.verify_hmac', true)) {
            $this->verifySignature($request, $providerInstance);
        }

        if (config('mts-webhooks.security.verify_timestamp', true)) {
            $this->verifyTimestamp($request, $providerInstance);
        }

        $this->verifyIpRestrictions($request);

        return $providerInstance;
    }

    public function resolveProvider(string $provider): WebhookProviderContract
    {
        $providerClass = self::PROVIDER_MAP[$provider] ?? self::PROVIDER_MAP['generic'];

        return new $providerClass();
    }

    private function verifySignature(Request $request, WebhookProviderContract $provider): void
    {
        $config = config('mts-webhooks.providers.' . $provider->configKey(), []);
        $secret = $config['secret'] ?? '';

        if ($secret === '' || $secret === null) {
            if (config('mts-webhooks.logging.enabled', true)) {
                Log::warning('Webhook signature verification skipped: no secret configured', [
                    'provider' => $provider->name(),
                ]);
            }

            return;
        }

        if (! $provider->verifySignature($request, $secret)) {
            throw new SignatureVerificationException(
                'Invalid webhook signature for provider: ' . $provider->name()
            );
        }
    }

    private function verifyTimestamp(Request $request, WebhookProviderContract $provider): void
    {
        $timestamp = $provider->getTimestampFromRequest($request);

        if ($timestamp === null) {
            return;
        }

        $tolerance = config('mts-webhooks.security.timestamp_tolerance', 300);
        $now = time();
        $diff = abs($now - $timestamp);

        if ($diff > $tolerance) {
            throw new ReplayAttackException(
                'Webhook timestamp is outside the allowed tolerance window. '
                . 'Diff: ' . $diff . 's, Tolerance: ' . $tolerance . 's'
            );
        }
    }

    private function verifyIpRestrictions(Request $request): void
    {
        $allowedIps = config('mts-webhooks.security.ip_restrictions', []);

        if (empty($allowedIps)) {
            return;
        }

        $clientIp = $request->ip();

        if (! in_array($clientIp, $allowedIps, true)) {
            throw new SignatureVerificationException(
                'Webhook request from unauthorized IP: ' . $clientIp
            );
        }
    }
}
