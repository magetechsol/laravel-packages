<?php

declare(strict_types=1);

namespace MageTech\Workflow\Console;

use Illuminate\Console\Command;
use MageTech\Workflow\Engine\WorkflowRepository;
use MageTech\Workflow\Models\Workflow;

class ListWorkflowsCommand extends Command
{
    protected $signature = 'mts:workflow:list {--json}';

    protected $description = 'List all registered workflow definitions';

    public function handle(WorkflowRepository $repository): int
    {
        $workflows = Workflow::orderBy('name')->get();

        if ($workflows->isEmpty()) {
            $this->info('No workflows registered.');

            return self::SUCCESS;
        }

        if ($this->option('json')) {
            $this->line($workflows->toJson(JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Description', 'Steps', 'Active', 'Created'],
            $workflows->map(fn ($w) => [
                $w->id,
                $w->name,
                $w->description ?? '-',
                count($w->definition['steps'] ?? []),
                $w->is_active ? 'Yes' : 'No',
                $w->created_at->format('Y-m-d H:i'),
            ])
        );

        $this->line('');
        $this->line("Total: {$workflows->count()} workflow(s)");

        return self::SUCCESS;
    }
}
