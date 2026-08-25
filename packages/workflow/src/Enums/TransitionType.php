<?php

declare(strict_types=1);

namespace MageTech\Workflow\Enums;

enum TransitionType: string
{
    case Started = 'started';
    case StepStarted = 'step.started';
    case StepCompleted = 'step.completed';
    case StepFailed = 'step.failed';
    case StepSkipped = 'step.skipped';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case Failed = 'failed';
    case Retried = 'retried';
    case Paused = 'paused';
    case Resumed = 'resumed';

    public function label(): string
    {
        return match ($this) {
            self::Started => 'Workflow Started',
            self::StepStarted => 'Step Started',
            self::StepCompleted => 'Step Completed',
            self::StepFailed => 'Step Failed',
            self::StepSkipped => 'Step Skipped',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Workflow Completed',
            self::Failed => 'Workflow Failed',
            self::Retried => 'Step Retried',
            self::Paused => 'Workflow Paused',
            self::Resumed => 'Workflow Resumed',
        };
    }
}
