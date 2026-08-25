<?php

declare(strict_types=1);

namespace MageTech\Workflow\Enums;

enum ApprovalType: string
{
    case Single = 'single';
    case Multiple = 'multiple';
    case AnyApprover = 'any';
    case AllApprovers = 'all';
    case RoleBased = 'role';
    case UserBased = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Single => 'Single Approver',
            self::Multiple => 'Multiple Approvers',
            self::AnyApprover => 'Any Approver',
            self::AllApprovers => 'All Approvers',
            self::RoleBased => 'Role-Based',
            self::UserBased => 'User-Based',
        };
    }

    public function requiresAll(): bool
    {
        return $this === self::AllApprovers;
    }
}
