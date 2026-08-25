<?php

declare(strict_types=1);

namespace MageTech\DevTools\Collectors;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class PerformanceCollector
{
    public function __construct(
        protected Repository $config,
    ) {
    }

    public function collect(): array
    {
        return [
            'requests' => $this->getRequestCount(),
            'queries' => $this->getQueryCount(),
            'slow_queries' => $this->getSlowQueries(),
            'jobs' => $this->getJobCount(),
            'failed_jobs' => $this->getFailedJobCount(),
            'cache' => $this->getCacheStats(),
        ];
    }

    public function getRequestCount(): int
    {
        $logPath = $this->getLogPath();

        if (! $logPath || ! file_exists($logPath)) {
            return 0;
        }

        $content = file_get_contents($logPath);

        return substr_count($content, 'request');
    }

    public function getQueryCount(): int
    {
        $logPath = $this->getLogPath();

        if (! $logPath || ! file_exists($logPath)) {
            return 0;
        }

        $content = file_get_contents($logPath);

        return substr_count($content, 'Query');
    }

    public function getSlowQueries(): array
    {
        $logPath = $this->getLogPath();
        $threshold = $this->config->get('mts-devtools.slow_query_threshold', 1000);

        if (! $logPath || ! file_exists($logPath)) {
            return [];
        }

        $slowQueries = [];
        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_contains($line, 'Query')) {
                if (preg_match('/(\d+\.?\d*)\s*ms/', $line, $matches)) {
                    $duration = (float) $matches[1];
                    if ($duration >= $threshold) {
                        $slowQueries[] = [
                            'query' => trim($line),
                            'duration_ms' => $duration,
                        ];
                    }
                }
            }
        }

        return array_slice($slowQueries, -50);
    }

    public function getJobCount(): int
    {
        try {
            return Queue::size();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function getFailedJobCount(): int
    {
        try {
            return (int) DB::table('failed_jobs')->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function getCacheStats(): array
    {
        try {
            $store = Cache::store();

            return [
                'driver' => $this->config->get('cache.default', 'unknown'),
                'hit' => method_exists($store, 'hits') ? $store->hits() : 0,
                'miss' => method_exists($store, 'misses') ? $store->misses() : 0,
            ];
        } catch (\Throwable) {
            return [
                'driver' => 'unknown',
                'hit' => 0,
                'miss' => 0,
            ];
        }
    }

    protected function getLogPath(): ?string
    {
        $customPath = $this->config->get('mts-devtools.log_path');

        if ($customPath && file_exists($customPath)) {
            return $customPath;
        }

        $logChannel = $this->config->get('logging.default', 'stack');
        $logFile = $this->config->get("logging.channels.{$logChannel}", []);

        if (isset($logFile['path'])) {
            return $logFile['path'];
        }

        $dailyPath = storage_path('logs/laravel.log');

        return file_exists($dailyPath) ? $dailyPath : null;
    }
}
