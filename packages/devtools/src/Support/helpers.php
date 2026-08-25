<?php

declare(strict_types=1);

use MageTech\DevTools\Support\Facades\DevTools;

if (! function_exists('devtools')) {
    /**
     * Resolve the DevTools instance.
     */
    function devtools(): MageTech\DevTools\DevTools
    {
        return DevTools::getFacadeRoot();
    }
}

if (! function_exists('devtools_enabled')) {
    /**
     * Check if DevTools is enabled.
     */
    function devtools_enabled(): bool
    {
        return devtools()->isEnabled();
    }
}
