<?php

declare(strict_types=1);

namespace MageTech\DevTools\Collectors;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Route;

class SecurityCollector
{
    public function __construct(
        protected Repository $config,
    ) {
    }

    public function collect(): array
    {
        return [
            'debug_mode' => $this->getDebugMode(),
            'environment' => $this->getEnvironment(),
            'configuration' => $this->getConfigStatus(),
            'routes' => $this->getRouteStatus(),
            'https' => $this->isHttps(),
            'php_extensions' => $this->getPhpExtensions(),
        ];
    }

    public function getDebugMode(): array
    {
        $debug = $this->config->get('app.debug', false);

        return [
            'enabled' => $debug,
            'status' => $debug ? 'ON' : 'OFF',
            'risk' => $debug ? 'high' : 'low',
        ];
    }

    public function getEnvironment(): array
    {
        $env = $this->config->get('app.env', 'unknown');
        $prodLike = in_array($env, ['production', 'prod']);

        return [
            'value' => $env,
            'is_production' => $prodLike,
            'status' => $prodLike ? 'Production' : 'Development',
        ];
    }

    public function getConfigStatus(): array
    {
        $cached = file_exists(config_path('services.php'));

        try {
            $configCached = $this->config->get('app.config_cached', false);
        } catch (\Throwable) {
            $configCached = false;
        }

        return [
            'cached' => $configCached,
            'status' => $configCached ? 'Cached' : 'Not Cached',
        ];
    }

    public function getRouteStatus(): array
    {
        $routes = Route::getRoutes();

        $methods = [];
        foreach ($routes as $route) {
            foreach ($route->methods() as $method) {
                $methods[$method] = ($methods[$method] ?? 0) + 1;
            }
        }

        return [
            'total' => $routes->count(),
            'methods' => $methods,
        ];
    }

    public function isHttps(): bool
    {
        return request()->isSecure();
    }

    public function getPhpExtensions(): array
    {
        $securityExtensions = [
            'openssl' => 'SSL/TLS encryption',
            'curl' => 'HTTP client',
            'mbstring' => 'Multibyte string handling',
            'xml' => 'XML parsing',
            'json' => 'JSON handling',
            'filter' => 'Data filtering',
            'hash' => 'Hashing functions',
            'session' => 'Session handling',
            'pdo' => 'Database abstraction',
        ];

        $loaded = get_loaded_extensions();
        $result = [];

        foreach ($securityExtensions as $ext => $description) {
            $result[$ext] = [
                'loaded' => in_array($ext, $loaded),
                'description' => $description,
            ];
        }

        return $result;
    }
}
