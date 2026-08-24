<?php

declare(strict_types=1);

use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit', 'Integration');

/**
 * Helper function to create a request with query parameters.
 */
function createRequest(string  = 'GET', string  = '/', array  = []): \Illuminate\Http\Request
{
    return \Illuminate\Http\Request::create(, , [], [], [], );
}
