<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MageTech\Webhooks\Exceptions\DuplicateWebhookException;
use MageTech\Webhooks\Exceptions\InvalidPayloadException;
use MageTech\Webhooks\Exceptions\ReplayAttackException;
use MageTech\Webhooks\Exceptions\SignatureVerificationException;
use MageTech\Webhooks\Inbound\Pipeline\WebhookPipeline;

class WebhookController
{
    public function handle(Request $request, string $provider): JsonResponse
    {
        try {
            /** @var WebhookPipeline $pipeline */
            $pipeline = app(WebhookPipeline::class);

            $webhook = $pipeline->handle($request, $provider);

            return response()->json([
                'status' => 'received',
                'webhook_id' => $webhook->id,
            ], 200);

        } catch (SignatureVerificationException $e) {
            if (config('mts-webhooks.logging.enabled', true)) {
                Log::warning('Webhook signature verification failed', [
                    'provider' => $provider,
                    'error' => $e->getMessage(),
                    'ip' => $request->ip(),
                ]);
            }

            return response()->json(['error' => 'Unauthorized'], 401);

        } catch (ReplayAttackException $e) {
            if (config('mts-webhooks.logging.enabled', true)) {
                Log::warning('Webhook replay attack detected', [
                    'provider' => $provider,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json(['error' => 'Replay attack detected'], 409);

        } catch (DuplicateWebhookException $e) {
            return response()->json(['status' => 'duplicate'], 200);

        } catch (InvalidPayloadException $e) {
            if (config('mts-webhooks.logging.enabled', true)) {
                Log::warning('Invalid webhook payload', [
                    'provider' => $provider,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json(['error' => 'Invalid payload'], 400);

        } catch (\Throwable $e) {
            if (config('mts-webhooks.logging.enabled', true)) {
                Log::error('Unexpected webhook processing error', [
                    'provider' => $provider,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return response()->json(['error' => 'Internal error'], 500);
        }
    }
}
