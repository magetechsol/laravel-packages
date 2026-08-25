<?php

declare(strict_types=1);

namespace MageTech\DevTools\Collectors;

use Illuminate\Config\Repository;
use Illuminate\Support\Composer;
use Symfony\Component\Process\Process;

class PackageCollector
{
    public function __construct(
        protected Repository $config,
        protected Composer $composer,
    ) {
    }

    public function collect(): array
    {
        return [
            'installed' => $this->getInstalledPackages(),
            'outdated' => $this->getOutdatedPackages(),
        ];
    }

    public function getInstalledPackages(): array
    {
        $composerPath = base_path('composer.json');

        if (! file_exists($composerPath)) {
            return [];
        }

        $composerJson = json_decode(file_get_contents($composerPath), true);

        $require = $composerJson['require'] ?? [];
        $requireDev = $composerJson['require-dev'] ?? [];

        $installed = [];

        foreach ($require as $name => $constraint) {
            $installed[$name] = [
                'version' => $this->getInstalledVersion($name),
                'constraint' => $constraint,
                'dev' => false,
            ];
        }

        foreach ($requireDev as $name => $constraint) {
            $installed[$name] = [
                'version' => $this->getInstalledVersion($name),
                'constraint' => $constraint,
                'dev' => true,
            ];
        }

        ksort($installed);

        return $installed;
    }

    public function getOutdatedPackages(): array
    {
        try {
            $process = Process::fromShellCommandline([
                'composer', 'outdated', '--direct', '--format=json', '--no-ansi',
            ]);

            $process->setTimeout(60);
            $process->run();

            if (! $process->isSuccessful()) {
                return [];
            }

            $output = json_decode($process->getOutput(), true);

            $outdated = [];
            foreach ($output['installed'] ?? [] as $package) {
                $outdated[$package['name']] = [
                    'current' => $package['version'] ?? 'N/A',
                    'latest' => $package['latest'] ?? 'N/A',
                    'upgrade' => $package['latest-version'] ?? 'N/A',
                    'target' => $package['target'] ?? 'N/A',
                ];
            }

            return $outdated;
        } catch (\Throwable) {
            return [];
        }
    }

    protected function getInstalledVersion(string $packageName): string
    {
        $lockPath = base_path('composer.lock');

        if (! file_exists($lockPath)) {
            return 'N/A';
        }

        $lockData = json_decode(file_get_contents($lockPath), true);

        foreach ($lockData['packages-dev'] ?? [] as $package) {
            if ($package['name'] === $packageName) {
                return $package['version'] ?? 'N/A';
            }
        }

        foreach ($lockData['packages'] ?? [] as $package) {
            if ($package['name'] === $packageName) {
                return $package['version'] ?? 'N/A';
            }
        }

        return 'N/A';
    }
}
