<?php

declare(strict_types=1);

namespace MageTech\DevTools;

use Illuminate\Config\Repository;
use MageTech\DevTools\Collectors\ApplicationCollector;
use MageTech\DevTools\Collectors\PackageCollector;
use MageTech\DevTools\Collectors\PerformanceCollector;
use MageTech\DevTools\Collectors\SecurityCollector;
use MageTech\DevTools\Enums\HealthStatus;

class DevTools
{
    protected ?array $applicationData = null;

    protected ?array $performanceData = null;

    protected ?array $securityData = null;

    protected ?array $packageData = null;

    public function __construct(
        protected Repository $config,
        protected ApplicationCollector $application,
        protected PerformanceCollector $performance,
        protected SecurityCollector $security,
        protected PackageCollector $packages,
    ) {
    }

    public function isEnabled(): bool
    {
        return (bool) $this->config->get('mts-devtools.enabled', false);
    }

    public function isDashboardEnabled(): bool
    {
        return $this->isEnabled() && (bool) $this->config->get('mts-devtools.dashboard', true);
    }

    public function isCommandEnabled(): bool
    {
        return $this->isEnabled() && (bool) $this->config->get('mts-devtools.commands', true);
    }

    public function isCollectorEnabled(string $name): bool
    {
        return (bool) $this->config->get("mts-devtools.collectors.{$name}", true);
    }

    public function isAllowedIp(?string $ip = null): bool
    {
        $ip = $ip ?? request()->ip();
        $allowed = $this->config->get('mts-devtools.allowed_ips', ['127.0.0.1', '::1']);

        if (in_array('*', $allowed)) {
            return true;
        }

        return in_array($ip, $allowed);
    }

    public function hasPassword(): bool
    {
        $password = $this->config->get('mts-devtools.password');

        return $password !== null && $password !== '';
    }

    public function verifyPassword(string $password): bool
    {
        $stored = $this->config->get('mts-devtools.password');

        if ($stored === null) {
            return true;
        }

        return hash_equals($stored, $password);
    }

    public function getApplicationData(): array
    {
        if ($this->applicationData === null) {
            $this->applicationData = $this->isCollectorEnabled('application')
                ? $this->application->collect()
                : [];
        }

        return $this->applicationData;
    }

    public function getPerformanceData(): array
    {
        if ($this->performanceData === null) {
            $this->performanceData = $this->isCollectorEnabled('performance')
                ? $this->performance->collect()
                : [];
        }

        return $this->performanceData;
    }

    public function getSecurityData(): array
    {
        if ($this->securityData === null) {
            $this->securityData = $this->isCollectorEnabled('security')
                ? $this->security->collect()
                : [];
        }

        return $this->securityData;
    }

    public function getPackageData(): array
    {
        if ($this->packageData === null) {
            $this->packageData = $this->isCollectorEnabled('packages')
                ? $this->packages->collect()
                : [];
        }

        return $this->packageData;
    }

    public function getAllData(): array
    {
        return [
            'application' => $this->getApplicationData(),
            'performance' => $this->getPerformanceData(),
            'security' => $this->getSecurityData(),
            'packages' => $this->getPackageData(),
        ];
    }

    public function getHealthStatus(): array
    {
        $checks = [];

        $appData = $this->getApplicationData();
        $secData = $this->getSecurityData();
        $perfData = $this->getPerformanceData();

        $checks['environment'] = [
            'label' => 'Environment',
            'status' => in_array($appData['environment'] ?? '', ['production', 'staging'])
                ? HealthStatus::Healthy
                : HealthStatus::Warning,
            'message' => 'Running in '.($appData['environment'] ?? 'unknown').' environment',
        ];

        $checks['debug_mode'] = [
            'label' => 'Debug Mode',
            'status' => ($secData['debug_mode']['enabled'] ?? false)
                ? HealthStatus::Critical
                : HealthStatus::Healthy,
            'message' => 'Debug mode is '.($secData['debug_mode']['status'] ?? 'unknown'),
        ];

        $checks['database'] = [
            'label' => 'Database',
            'status' => HealthStatus::Healthy,
            'message' => ($appData['database']['driver'] ?? 'unknown').' connected',
        ];

        $slowQueries = $perfData['slow_queries'] ?? [];
        $checks['slow_queries'] = [
            'label' => 'Slow Queries',
            'status' => count($slowQueries) > 10
                ? HealthStatus::Critical
                : (count($slowQueries) > 0 ? HealthStatus::Warning : HealthStatus::Healthy),
            'message' => count($slowQueries).' slow queries detected',
        ];

        $failedJobs = $perfData['failed_jobs'] ?? 0;
        $checks['failed_jobs'] = [
            'label' => 'Failed Jobs',
            'status' => $failedJobs > 0
                ? HealthStatus::Warning
                : HealthStatus::Healthy,
            'message' => $failedJobs.' failed jobs',
        ];

        return $checks;
    }

    public function getOverallHealth(): HealthStatus
    {
        $checks = $this->getHealthStatus();
        $statuses = array_column($checks, 'status');

        if (in_array(HealthStatus::Critical, $statuses)) {
            return HealthStatus::Critical;
        }

        if (in_array(HealthStatus::Warning, $statuses)) {
            return HealthStatus::Warning;
        }

        return HealthStatus::Healthy;
    }
}
