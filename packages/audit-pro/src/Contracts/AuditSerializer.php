<?php

declare(strict_types=1);

namespace MageTech\Audit\Contracts;

interface AuditSerializer
{
    public function serialize(mixed $value): mixed;

    public function unserialize(mixed $value): mixed;

    public function serializeModel($model): array;
}
