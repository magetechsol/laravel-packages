<?php

declare(strict_types=1);

namespace MageTech\Audit\Services;

use Illuminate\Http\Request;
use MageTech\Audit\Contracts\ActorResolver;
use MageTech\Audit\Support\ActorData;

class AuthenticatedUserResolver implements ActorResolver
{
    public function resolve(Request $request): ?ActorData
    {
        $user = $request->user();

        if ($user === null) {
            return new ActorData(
                type: config('audit.actor.default_type', 'system'),
                id: null,
                name: 'System',
                email: null,
            );
        }

        return new ActorData(
            type: get_class($user),
            id: $user->getAuthIdentifier(),
            name: $user->getAttribute('name') ?? $user->getAttribute('username'),
            email: $user->getAttribute('email'),
        );
    }
}
