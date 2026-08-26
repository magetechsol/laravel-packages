<?php

declare(strict_types=1);

namespace MageTech\Audit\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;
use MageTech\Audit\Models\Audit;

class AuditPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can(config('audit.permissions.view', 'audit.view'));
    }

    public function view(User $user, Audit $audit): bool
    {
        return $user->can(config('audit.permissions.view_details', 'audit.view_details'));
    }

    public function export(User $user): bool
    {
        return $user->can(config('audit.permissions.export', 'audit.export'));
    }

    public function delete(User $user, Audit $audit): bool
    {
        return $user->can(config('audit.permissions.delete', 'audit.delete'));
    }

    public function manage(User $user): bool
    {
        return $user->can(config('audit.permissions.manage', 'audit.manage'));
    }
}
