<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MageTech\AIGateway\Ai;
use MageTech\AIGateway\DTOs\UsageData;
use MageTech\AIGateway\Prompts\PromptBuilder;

/**
 * @method static PromptBuilder prompt(string $name)
 * @method static mixed send(string $prompt, ?string $provider = null, ?string $model = null, ?float $temperature = null, ?int $maxTokens = null, ?int $tenantId = null, ?int $userId = null, array $options = [])
 * @method static void recordUsage(UsageData $usage)
 * @method static void fake(?array $responses = null)
 * @method static void restore()
 * @method static void assertPrompted(string $name)
 * @method static void assertNotPrompted(string $name)
 * @method static void assertUsedModel(string $model)
 * @method static void assertUsedProvider(string $provider)
 * @method static void assertTokens(int $min, ?int $max = null)
 * @method static array getRecordedPrompts()
 * @method static array getRecordedModels()
 * @method static array getRecordedTokens()
 * @method static array getRecordedProviders()
 *
 * @see \MageTech\AIGateway\Ai
 */
class AI extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Ai::class;
    }
}
