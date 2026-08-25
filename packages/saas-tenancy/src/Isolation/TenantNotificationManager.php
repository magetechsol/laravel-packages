<?php

declare(strict_types=1);

namespace MageTech\SaaS\Isolation;

use Illuminate\Config\Repository;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;

class TenantNotificationManager
{
    public function __construct(
        protected Repository $config,
    ) {}

    public function getDatabase(?string $tenantId = null): ?string
    {
        if (! $this->config->get('mts-saas.notifications.enabled', true)) {
            return null;
        }

        return $this->config->get('mts-saas.migrations.connection')
            ?? config('database.default');
    }

    public function routeNotificationForMail(?string $tenantId = null): ?array
    {
        $tenant = \MageTech\SaaS\Models\Tenant::find($tenantId ?? tenant_id());

        return $tenant?->settings['notification_email'] ?? null;
    }

    public function shouldNotify(?string $tenantId = null): bool
    {
        $tenant = \MageTech\SaaS\Models\Tenant::find($tenantId ?? tenant_id());

        return $tenant?->isActive() ?? false;
    }

    public function getChannels(?string $tenantId = null): array
    {
        $tenant = \MageTech\SaaS\Models\Tenant::find($tenantId ?? tenant_id());

        return $tenant?->settings['notification_channels'] ?? ['mail', 'database'];
    }
}
