<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;
use MageTech\FeatureFlags\Models\FeatureFlag;

class FeatureFlagPolicy
{
    use HandlesAuthorization;

    public function viewManageFeatureFlags(Authenticatable $user): bool
    {
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin') || $user->hasRole('feature-flag-manager');
        }

        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo('manage-feature-flags');
        }

        return false;
    }

    public function viewFeatureFlags(Authenticatable $user): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return $this->viewManageFeatureFlags($user);
    }

    public function update(Authenticatable $user, FeatureFlag $flag): bool
    {
        return $this->viewManageFeatureFlags($user);
    }

    public function delete(Authenticatable $user, FeatureFlag $flag): bool
    {
        return $this->viewManageFeatureFlags($user);
    }

    public function enable(Authenticatable $user, FeatureFlag $flag): bool
    {
        return $this->viewManageFeatureFlags($user);
    }

    public function disable(Authenticatable $user, FeatureFlag $flag): bool
    {
        return $this->viewManageFeatureFlags($user);
    }

    public function evaluate(Authenticatable $user, FeatureFlag $flag): bool
    {
        return $this->viewFeatureFlags($user);
    }
}
