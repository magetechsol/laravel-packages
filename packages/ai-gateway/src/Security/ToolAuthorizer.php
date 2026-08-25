<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Security;

use Illuminate\Config\Repository;

class ToolAuthorizer
{
    public function __construct(
        protected Repository $config,
    ) {}

    public function isToolAllowed(string $toolName, ?int $tenantId = null): bool
    {
        if (! $this->config->get('mts-ai.security.tool_authorization', true)) {
            return true;
        }

        $globalDenied = $this->config->get('mts-ai.security.denylisted_tools', []);

        if (in_array($toolName, $globalDenied, true)) {
            return false;
        }

        $globalAllowed = $this->config->get('mts-ai.security.allowlisted_tools');

        if ($globalAllowed !== null && ! in_array($toolName, $globalAllowed, true)) {
            return false;
        }

        if ($tenantId) {
            $tenantTools = $this->config->get("mts-ai.security.tenant_tools.{$tenantId}", null);

            if ($tenantTools !== null) {
                return in_array($toolName, $tenantTools['allow'] ?? [], true)
                    && ! in_array($toolName, $tenantTools['deny'] ?? [], true);
            }
        }

        return true;
    }

    public function authorizeTool(string $toolName, ?int $tenantId = null): void
    {
        if (! $this->isToolAllowed($toolName, $tenantId)) {
            throw new \MageTech\AIGateway\Exceptions\AiGatewayException(
                "Tool [{$toolName}] is not authorized" .
                ($tenantId ? " for tenant [{$tenantId}]" : '') . "."
            );
        }
    }

    public function getAllowedTools(?int $tenantId = null): array
    {
        $allTools = $this->config->get('mts-ai.security.available_tools', []);

        return array_filter($allTools, function ($tool) use ($tenantId) {
            return $this->isToolAllowed($tool, $tenantId);
        });
    }
}
