<?php

declare(strict_types=1);

namespace MageTech\Workflow\Exceptions;

class WorkflowAuthorizationException extends WorkflowException
{
    public static function notAuthorized(string $action, string $workflowName = ''): static
    {
        $message = "You are not authorized to {$action}";
        if ($workflowName !== '') {
            $message .= " workflow [{$workflowName}]";
        }

        return new static($message . '.');
    }

    public static function cannotApprove(int|string $userId, string $stepName): static
    {
        return new static("User [{$userId}] is not authorized to approve step [{$stepName}].");
    }

    public static function cannotCancel(int|string $userId, int|string $instanceId): static
    {
        return new static("User [{$userId}] is not authorized to cancel workflow instance [{$instanceId}].");
    }
}
