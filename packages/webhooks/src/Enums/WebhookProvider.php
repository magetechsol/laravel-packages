<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Enums;

enum WebhookProvider: string
{
    case Stripe = 'stripe';
    case Razorpay = 'razorpay';
    case Shopify = 'shopify';
    case Magento = 'magento';
    case Generic = 'generic';

    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Stripe',
            self::Razorpay => 'Razorpay',
            self::Shopify => 'Shopify',
            self::Magento => 'Magento',
            self::Generic => 'Generic',
        };
    }

    public function configKey(): string
    {
        return match ($this) {
            self::Stripe, self::Razorpay, self::Shopify, self::Magento, self::Generic
                => $this->value,
        };
    }
}
