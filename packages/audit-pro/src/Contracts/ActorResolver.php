<?php

declare(strict_types=1);

namespace MageTech\Audit\Contracts;

use Illuminate\Http\Request;

interface ActorResolver
{
    public function resolve(Request $request): ?ActorData;
}
