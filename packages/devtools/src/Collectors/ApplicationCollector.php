<?php

declare(strict_types=1);

namespace MageTech\DevTools\Collectors;

use Illuminate\Config\Repository;
use Illuminate\Database\DatabaseManager;

class ApplicationCollector
{
    public function __construct(
        protected Repository $config,
        protected DatabaseManager $db,
    ) {
    }

    public function collect(): array
    {
        return [
            'laravel' => $this->getLaravelVersion(),
            'php' => $this->getPhpVersion(),
            'environment' => $this->getEnvironment(),
            'database' => $this->getDatabase(),
            'cache' => $this->getCache(),
            'queue' => $this->getQueue(),
        ];
    }

    public function getLaravelVersion(): string
    {
        return app()->version();
    }

    public function getPhpVersion(): string
    {
        return PHP_VERSION;
    }

    public function getEnvironment(): string
    {
        return $this->config->get('app.env', 'unknown');
    }

    public function getDatabase(): array
    {
        $default = $this->config->get('database.default', 'unknown');
        $connection = $this->config->get("database.connections.{$default}", []);

        $version = null;

        try {
            $version = $this->db->connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Throwable) {
            $version = 'N/A';
        }

        return [
            'driver' => $default,
            'host' => $connection['host'] ?? 'N/A',
            'database' => $connection['database'] ?? 'N/A',
            'version' => $version,
        ];
    }

    public function getCache(): array
    {
        $driver = $this->config->get('cache.default', 'unknown');

        $stores = [];
        foreach ($this->config->get('cache.stores', []) as $name => $store) {
            $stores[$name] = $store['driver'] ?? 'unknown';
        }

        return [
            'default' => $driver,
            'stores' => $stores,
        ];
    }

    public function getQueue(): array
    {
        $driver = $this->config->get('queue.default', 'unknown');

        $connections = [];
        foreach ($this->config->get('queue.connections', []) as $name => $connection) {
            $connections[$name] = $connection['driver'] ?? 'unknown';
        }

        return [
            'default' => $driver,
            'connections' => $connections,
        ];
    }
}
