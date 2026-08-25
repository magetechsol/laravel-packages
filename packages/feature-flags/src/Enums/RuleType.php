<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Enums;

enum RuleType: string
{
    case UserId = 'user_id';
    case Email = 'email';
    case Role = 'role';
    case Permission = 'permission';
    case Team = 'team';
    case Organization = 'organization';
    case Tenant = 'tenant';
    case Country = 'country';
    case Locale = 'locale';
    case Ip = 'ip';
    case Environment = 'environment';
    case Device = 'device';
    case Attribute = 'attribute';

    public function label(): string
    {
        return match ($this) {
            self::UserId => 'User ID',
            self::Email => 'Email',
            self::Role => 'Role',
            self::Permission => 'Permission',
            self::Team => 'Team',
            self::Organization => 'Organization',
            self::Tenant => 'Tenant',
            self::Country => 'Country',
            self::Locale => 'Locale',
            self::Ip => 'IP Address',
            self::Environment => 'Environment',
            self::Device => 'Device',
            self::Attribute => 'Custom Attribute',
        };
    }

    public function requiresSubject(): bool
    {
        return ! in_array($this, [
            self::Environment,
        ]);
    }
}
