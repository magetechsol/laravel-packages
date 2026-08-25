<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Events;

use Illuminate\Foundation\Events\Dispatchable;
use MageTech\AIGateway\DTOs\PromptTemplate;

class AiPromptResolved
{
    use Dispatchable;

    public function __construct(
        public readonly PromptTemplate $template,
    ) {}
}
