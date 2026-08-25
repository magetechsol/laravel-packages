<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Feature;

use MageTech\FeatureFlags\Tests\TestCase;

class ArtisanCommandsTest extends TestCase
{
    public function test_list_command(): void
    {
        $this->artisan('mts:feature-flags:list')
            ->expectsOutput('No feature flags found.')
            ->assertExitCode(0);
    }

    public function test_enable_disable_commands(): void
    {
        \MageTech\FeatureFlags\Models\FeatureFlag::create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'key' => 'cli-flag',
            'name' => 'CLI Flag',
            'type' => 'boolean',
            'enabled' => false,
        ]);

        $this->artisan('mts:feature-flags:enable', ['key' => 'cli-flag'])
            ->expectsOutput('Feature flag [cli-flag] has been enabled.')
            ->assertExitCode(0);

        $this->artisan('mts:feature-flags:disable', ['key' => 'cli-flag'])
            ->expectsOutput('Feature flag [cli-flag] has been disabled.')
            ->assertExitCode(0);
    }

    public function test_check_command(): void
    {
        \MageTech\FeatureFlags\Models\FeatureFlag::create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'key' => 'check-flag',
            'name' => 'Check Flag',
            'type' => 'boolean',
            'enabled' => true,
            'environment' => 'testing',
        ]);

        $this->artisan('mts:feature-flags:check', ['key' => 'check-flag'])
            ->assertExitCode(0);
    }

    public function test_clear_cache_command(): void
    {
        $this->artisan('mts:feature-flags:clear-cache')
            ->expectsOutput('All feature flags cache cleared.')
            ->assertExitCode(0);
    }

    public function test_enable_nonexistent_flag(): void
    {
        $this->artisan('mts:feature-flags:enable', ['key' => 'nonexistent'])
            ->expectsOutput('Feature flag [nonexistent] not found.')
            ->assertExitCode(1);
    }

    public function test_export_command(): void
    {
        $this->artisan('mts:feature-flags:export')
            ->assertExitCode(0);
    }
}
