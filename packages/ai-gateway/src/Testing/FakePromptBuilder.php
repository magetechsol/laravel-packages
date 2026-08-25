<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Testing;

use MageTech\AIGateway\Ai;
use MageTech\AIGateway\DTOs\PromptTemplate;
use MageTech\AIGateway\Prompts\PromptBuilder;

class FakePromptBuilder extends PromptBuilder
{
    public function __construct(
        PromptTemplate $template,
        Ai $gateway,
    ) {
        parent::__construct($template, $gateway);
    }

    public function generate(): mixed
    {
        return parent::generate();
    }

    public function generateStream(): mixed
    {
        return parent::generateStream();
    }
}
