<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Unit\Support;

use MageTech\FeatureFlags\Support\PercentageRollout;
use PHPUnit\Framework\TestCase;

class PercentageRolloutTest extends TestCase
{
    private PercentageRollout $rollout;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rollout = new PercentageRollout();
    }

    public function test_zero_percent_always_false(): void
    {
        $result = $this->rollout->determine('test-flag', 'user-1', 0);

        $this->assertFalse($result);
    }

    public function test_hundred_percent_always_true(): void
    {
        $result = $this->rollout->determine('test-flag', 'user-1', 100);

        $this->assertTrue($result);
    }

    public function test_deterministic_for_same_input(): void
    {
        $result1 = $this->rollout->determine('test-flag', 'user-1', 50);
        $result2 = $this->rollout->determine('test-flag', 'user-1', 50);

        $this->assertSame($result1, $result2);
    }

    public function test_different_keys_produce_different_results(): void
    {
        $results = [];
        for ($i = 0; $i < 100; $i++) {
            $results[] = $this->rollout->determine('flag-a', "user-{$i}", 50);
        }

        $trueCount = count(array_filter($results));

        $this->assertGreaterThan(0, $trueCount);
        $this->assertLessThan(100, $trueCount);
    }

    public function test_50_percent_approximately_half(): void
    {
        $trueCount = 0;
        $total = 1000;

        for ($i = 0; $i < $total; $i++) {
            if ($this->rollout->determine('test-flag', "user-{$i}", 50)) {
                $trueCount++;
            }
        }

        $this->assertGreaterThan(400, $trueCount);
        $this->assertLessThan(600, $trueCount);
    }

    public function test_variant_returns_consistent_result(): void
    {
        $variants = [
            ['key' => 'a', 'name' => 'A', 'weight' => 1, 'enabled' => true],
            ['key' => 'b', 'name' => 'B', 'weight' => 1, 'enabled' => true],
        ];

        $result1 = $this->rollout->getVariant('test-flag', 'user-1', $variants);
        $result2 = $this->rollout->getVariant('test-flag', 'user-1', $variants);

        $this->assertSame($result1, $result2);
        $this->assertContains($result1, ['a', 'b']);
    }

    public function test_variant_respects_weights(): void
    {
        $variants = [
            ['key' => 'a', 'name' => 'A', 'weight' => 9, 'enabled' => true],
            ['key' => 'b', 'name' => 'B', 'weight' => 1, 'enabled' => true],
        ];

        $counts = ['a' => 0, 'b' => 0];

        for ($i = 0; $i < 1000; $i++) {
            $variant = $this->rollout->getVariant('test-flag', "user-{$i}", $variants);
            $counts[$variant]++;
        }

        $this->assertGreaterThan($counts['b'], $counts['a']);
    }
}
