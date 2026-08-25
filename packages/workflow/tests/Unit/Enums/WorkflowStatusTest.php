<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Unit\Enums;

use MageTech\Workflow\Enums\WorkflowStatus;
use PHPUnit\Framework\TestCase;

class WorkflowStatusTest extends TestCase
{
    public function test_label_returns_human_readable_name(): void
    {
        $this->assertSame('Running', WorkflowStatus::Running->label());
        $this->assertSame('Completed', WorkflowStatus::Completed->label());
        $this->assertSame('Failed', WorkflowStatus::Failed->label());
        $this->assertSame('Cancelled', WorkflowStatus::Cancelled->label());
    }

    public function test_is_active_returns_true_for_active_statuses(): void
    {
        $this->assertTrue(WorkflowStatus::Draft->isActive());
        $this->assertTrue(WorkflowStatus::Running->isActive());
        $this->assertTrue(WorkflowStatus::Paused->isActive());
    }

    public function test_is_active_returns_false_for_terminal_statuses(): void
    {
        $this->assertFalse(WorkflowStatus::Completed->isActive());
        $this->assertFalse(WorkflowStatus::Failed->isActive());
        $this->assertFalse(WorkflowStatus::Cancelled->isActive());
    }

    public function test_is_terminal_returns_true_for_terminal_statuses(): void
    {
        $this->assertTrue(WorkflowStatus::Completed->isTerminal());
        $this->assertTrue(WorkflowStatus::Failed->isTerminal());
        $this->assertTrue(WorkflowStatus::Cancelled->isTerminal());
    }

    public function test_can_retry_only_for_failed(): void
    {
        $this->assertTrue(WorkflowStatus::Failed->canRetry());
        $this->assertFalse(WorkflowStatus::Completed->canRetry());
        $this->assertFalse(WorkflowStatus::Running->canRetry());
    }

    public function test_can_cancel_for_active_statuses(): void
    {
        $this->assertTrue(WorkflowStatus::Draft->canCancel());
        $this->assertTrue(WorkflowStatus::Running->canCancel());
        $this->assertTrue(WorkflowStatus::Paused->canCancel());
        $this->assertFalse(WorkflowStatus::Completed->canCancel());
    }
}
