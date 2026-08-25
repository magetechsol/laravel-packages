<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;

class FeatureFlagRule extends Model
{
    protected $table = 'mts_feature_flag_rules';

    protected $fillable = [
        'feature_flag_id',
        'rule_type',
        'operator',
        'attribute',
        'value',
        'priority',
        'enabled',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'enabled' => 'boolean',
            'metadata' => 'array',
            'rule_type' => RuleType::class,
            'operator' => RuleOperator::class,
        ];
    }

    public function featureFlag(): BelongsTo
    {
        return $this->belongsTo(FeatureFlag::class, 'feature_flag_id');
    }
}
