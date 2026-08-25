<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Unit\Support;

use MageTech\Workflow\Enums\RetryBackoff;
use MageTech\Workflow\Support\RetryStrategy;
use PHPUnit\Framework\TestCase;

class RetryStrategyTest extends TestCase
{
    private RetryStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new RetryStrategy();
    }

    public function test_calculate_delay_fixed(): void
    {
        $delay = $this->strategy->calculateDelay(1, RetryBackoff::Fixed, 60);
        $this->assertSame(60, $delay);
    }

    public function test_calculate_delay_linear(): void
    {
        $delay1 = $this->strategy->calculateDelay(1, RetryBackoff::Linear, 60);
        $delay2 = $this->strategy->calculateDelay(2, RetryBackoff::Linear, 60);
        $this->assertSame(60, $delay1);
        $this->assertSame(120, $delay2);
    }

    public function test_calculate_delay_exponential(): void
    {
        $delay1 = $this->strategy->calculateDelay(1, RetryBackoff::Exponential, 60);
        $delay2 = $this->strategy->calculateDelay(2, RetryBackoff::Exponential, 60);
        $this->assertSame(60, $delay1);
        $this->assertSame(120, $delay2);
    }

    public function test_calculate_delay_respects_max(): void
    {
        $delay = $this->strategy->calculateDelay(10, RetryBackoff::Exponential, 60, 300);
        $this->assertSame(300, $delay);
    }

    public function test_calculate_next_retry_returns_future_time(): void
    {
        $nextRetry = $this->strategy->calculateNextRetry(0, RetryBackoff::Exponential, 60);
        $this->assertGreaterThan(now(), $nextRetry);
    }
}
