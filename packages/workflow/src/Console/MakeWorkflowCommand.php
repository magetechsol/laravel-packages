<?php

declare(strict_types=1);

namespace MageTech\Workflow\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeWorkflowCommand extends Command
{
    protected $signature = 'mts:workflow:make {name} {--path=}';

    protected $description = 'Create a new workflow class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $className = Str::studly($name);

        if (! str_ends_with($className, 'Workflow')) {
            $className .= 'Workflow';
        }

        $path = $this->option('path') ?? app_path('Workflows');
        $filename = $path . '/' . $className . '.php';

        if (file_exists($filename)) {
            $this->error("Workflow [{$filename}] already exists.");

            return self::FAILURE;
        }

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $stub = $this->getStub();

        $content = str_replace(
            ['{{ class }}', '{{ name }}'],
            [$className, Str::headline($name)],
            $stub,
        );

        file_put_contents($filename, $content);

        $this->info("Workflow [{$filename}] created successfully.");
        $this->line('');
        $this->line('Next steps:');
        $this->line("  1. Open {$filename}");
        $this->line('  2. Define your workflow steps in the build() method');
        $this->line('  3. Register it: app(WorkflowRegistrar::class)->register($workflow)');

        return self::SUCCESS;
    }

    private function getStub(): string
    {
        return <<<'STUB'
<?php

declare(strict_types=1);

namespace App\Workflows;

use MageTech\Workflow\Definition\WorkflowDefinition;

class {{ class }}
{
    public function build(): WorkflowDefinition
    {
        return WorkflowDefinition::define('{{ name }}')
            ->description('{{ name }} workflow')
            ->step('step_one')
                ->timeout(30)
                ->maxAttempts(3)
            ->complete();
    }
}
STUB;
    }
}
