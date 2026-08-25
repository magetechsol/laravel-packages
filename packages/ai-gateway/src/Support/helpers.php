<?php

declare(strict_types=1);

use MageTech\AIGateway\Ai;
use MageTech\AIGateway\Support\Facades\AI;

if (! function_exists('ai')) {
    /**
     * Resolve the AI Gateway instance.
     */
    function ai(): Ai
    {
        return AI::getFacadeRoot();
    }
}

if (! function_exists('ai_prompt')) {
    /**
     * Create a prompt from a named template.
     */
    function ai_prompt(string $name): \MageTech\AIGateway\Prompts\PromptBuilder
    {
        return AI::prompt($name);
    }
}

if (! function_exists('ai_mask_pii')) {
    /**
     * Mask personally identifiable information in a string.
     */
    function ai_mask_pii(string $value): string
    {
        $masker = app(\MageTech\AIGateway\Security\PiiMasker::class);

        return $masker->mask($value);
    }
}
