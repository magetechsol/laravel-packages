<?php

declare(strict_types=1);

namespace MageTech\Audit\Contracts;

interface Auditable
{
    public function audits();

    public function getAuditEventTypes(): array;

    public function getAuditExcludeAttributes(): array;

    public function getAuditMaskedAttributes(): array;

    public function getAuditMetadata(): array;

    public function getAuditTags(): array;

    public function isAuditEnabled(): bool;

    public function tapAudit(array $attributes): array;
}
