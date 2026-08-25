<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Unit\Enums;

use MageTech\Workflow\Enums\StepStatus;
use PHPUnit\Framework\TestCase;

class StepStatusTest extends TestCase
{
    public function test_label(): void
    {
        $this->assertSame('Pending', StepStatus::Pending->label());
        $this->assertSame('Running', StepStatus::Running->label());
        $this->assertSame('Completed', StepStatus::Completed->label());
        $this->assertSame('Failed', StepStatus::Failed->label());
    }

    public function test_is_active(): void
    {
        $this->assertTrue(StepStatus::Pending->isActive());
        $this->assertTrue(StepStatus::Running->isActive());
        $this->assertFalse(StepStatus::Completed->isActive());
        $this->assertFalse(StepStatus::Failed->isActive());
    }

    public function test_is_terminal(): void
    {
        $this->assertTrue(StepStatus::Completed->isTerminal());
        $this->assertTrue(StepStatus::Failed->isTerminal());
        $this->assertTrue(StepStatus::Skipped->isTerminal());
        $this->assertFalse(StepStatus::Pending->isTerminal());
    }
}
