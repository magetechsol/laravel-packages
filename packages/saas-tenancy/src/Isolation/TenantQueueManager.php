<?php

declare(strict_types=1);

namespace MageTech\SaaS\Isolation;

use Illuminate\Config\Repository;

class TenantQueueManager
{
    public function __construct(
        protected Repository $config,
    ) {}

    public function prefix(?string $tenantId = null): string
    {
        $tenantId = $tenantId ?? tenant_id();
        $prefix = $this->config->get('mts-saas.queue.prefix', 'tenant');

        return "{$prefix}:{$tenantId}";
    }

    public function queueName(?string $queue = null): string
    {
        $queue = $queue ?? config('queue.default');
        $prefix = $this->prefix();

        return "{$prefix}:{$queue}";
    }

    public function connection(): ?string
    {
        return $this->config->get('mts-saas.queue.default_connection')
            ?? config('queue.default');
    }

    public function forTenant(?string $tenantId = null): array
    {
        return [
            'queue' => $this->queueName(),
            'connection' => $this->connection(),
        ];
    }

    public function dispatchJob(object $job, ?string $queue = null): void
    {
        $queue = $this->queueName($queue);
        $connection = $this->connection();

        dispatch($job)->onQueue($queue)->onConnection($connection);
    }

    public function getPrefix(): string
    {
        return $this->config->get('mts-saas.queue.prefix', 'tenant');
    }
}
