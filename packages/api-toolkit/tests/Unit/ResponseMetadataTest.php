<?php

declare(strict_types=1);

use MageTech\ApiToolkit\DTOs\ResponseMetadata;

test('response metadata creates from request attributes', function () {
    $metadata = ResponseMetadata::fromRequest([
        'request_id' => 'req_abc123',
        'correlation_id' => 'corr_xyz789',
        'api_version' => 'v1',
        'timestamp' => '2026-08-24T12:00:00Z',
    ]);

    expect($metadata->requestId)->toBe('req_abc123')
        ->and($metadata->correlationId)->toBe('corr_xyz789')
        ->and($metadata->apiVersion)->toBe('v1')
        ->and($metadata->timestamp)->toBe('2026-08-24T12:00:00Z');
});

test('response metadata converts to array', function () {
    $metadata = new ResponseMetadata(
        requestId: 'req_abc123',
        correlationId: 'corr_xyz789',
        apiVersion: 'v1',
        timestamp: '2026-08-24T12:00:00Z',
    );

    $array = $metadata->toArray();

    expect($array)->toHaveKeys(['request_id', 'correlation_id', 'api_version', 'timestamp'])
        ->and($array['request_id'])->toBe('req_abc123')
        ->and($array['correlation_id'])->toBe('corr_xyz789')
        ->and($array['api_version'])->toBe('v1')
        ->and($array['timestamp'])->toBe('2026-08-24T12:00:00Z');
});

test('response metadata excludes null values', function () {
    $metadata = new ResponseMetadata(
        requestId: 'req_abc123',
    );

    $array = $metadata->toArray();

    expect($array)->toHaveKey('request_id')
        ->and($array)->not->toHaveKey('correlation_id')
        ->and($array)->not->toHaveKey('api_version')
        ->and($array)->not->toHaveKey('timestamp');
});

test('response metadata handles all null values', function () {
    $metadata = new ResponseMetadata();

    $array = $metadata->toArray();

    expect($array)->toBeEmpty();
});
