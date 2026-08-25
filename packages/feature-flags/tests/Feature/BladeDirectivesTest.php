<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Feature;

use MageTech\FeatureFlags\Services\FeatureFlagService;
use MageTech\FeatureFlags\Tests\Fixtures\User;
use MageTech\FeatureFlags\Tests\TestCase;

class BladeDirectivesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        User::createTable();
    }

    protected function tearDown(): void
    {
        User::dropTable();
        parent::tearDown();
    }

    public function test_feature_directive(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'blade-feature',
            'name' => 'Blade Feature',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $blade = <<<'BLADE'
@testable
@feature('blade-feature')
visible
@endfeature
@endtest
BLADE;

        $result = \Illuminate\Support\Facades\Blade::render($blade);

        $this->assertStringContainsString('visible', $result);
    }

    public function test_feature_directive_when_disabled(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'blade-disabled',
            'name' => 'Blade Disabled',
            'type' => 'boolean',
            'enabled' => false,
        ]);

        $blade = <<<'BLADE'
@testable
@feature('blade-disabled')
visible
@endfeature
@endtest
BLADE;

        $result = \Illuminate\Support\Facades\Blade::render($blade);

        $this->assertStringNotContainsString('visible', $result);
    }

    public function test_unless_feature_directive(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'unless-flag',
            'name' => 'Unless Flag',
            'type' => 'boolean',
            'enabled' => false,
        ]);

        $blade = <<<'BLADE'
@testable
@unlessfeature('unless-flag')
visible
@endunlessfeature
@endtest
BLADE;

        $result = \Illuminate\Support\Facades\Blade::render($blade);

        $this->assertStringContainsString('visible', $result);
    }
}
