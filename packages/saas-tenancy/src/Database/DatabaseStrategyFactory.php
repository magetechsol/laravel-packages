<?php

declare(strict_types=1);

namespace MageTech\SaaS\Database;

use Illuminate\Config\Repository;
use MageTech\SaaS\Contracts\DatabaseStrategyContract;

class DatabaseStrategyFactory
{
    public function __construct(
        protected Repository $config,
    ) {}

    public function create(): DatabaseStrategyContract
    {
        $strategy = $this->config->get('mts-saas.strategy', 'shared');

        return match ($strategy) {
            'database' => new DatabasePerTenantStrategy($this->config),
            default => new SharedDatabaseStrategy($this->config),
        };
    }
}
