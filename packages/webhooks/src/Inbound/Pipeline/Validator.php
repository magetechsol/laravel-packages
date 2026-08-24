<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Pipeline;

use Illuminate\Http\Request;
use MageTech\Webhooks\Exceptions\InvalidPayloadException;

class Validator
{
    public function validate(Request $request, string $provider): array
    {
        $payload = $this->extractPayload($request);

        $this->validatePayloadStructure($payload);

        return $payload;
    }

    private function extractPayload(Request $request): array
    {
        $content = $request->getContent();

        if ($content === '' || $content === null) {
            throw new InvalidPayloadException('Webhook payload is empty');
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidPayloadException(
                'Invalid JSON payload: ' . json_last_error_msg()
            );
        }

        if (! is_array($decoded)) {
            throw new InvalidPayloadException('Webhook payload must be a JSON object');
        }

        return $decoded;
    }

    private function validatePayloadStructure(array $payload): void
    {
        if (empty($payload) && $payload !== []) {
            throw new InvalidPayloadException('Webhook payload cannot be null');
        }
    }
}
