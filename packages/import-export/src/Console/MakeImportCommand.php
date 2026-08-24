<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'mts:make:import', description: 'Create a new Import class')]
class MakeImportCommand extends Command
{
    protected $signature = 'mts:make:import {name : The name of the import class}';

    protected $description = 'Scaffold a new Import class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $className = Str::studly($name).'Import';
        $namespace = app()->getNamespace().'Imports';
        $path = app_path("Imports/{$className}.php");

        if (file_exists($path)) {
            $this->error("Import class '{$className}' already exists.");

            return Command::FAILURE;
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $stub = $this->getStub();

        $content = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $className],
            $stub,
        );

        file_put_contents($path, $content);

        $this->info("Import class created: {$path}");
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Define your column mapping in the map() method');
        $this->line('  2. Add validation rules in the rules() method');
        $this->line('  3. Optionally add transform logic');
        $this->newLine();

        return Command::SUCCESS;
    }

    private function getStub(): string
    {
        return <<<'STUB'
<?php

declare(strict_types=1);

{{ namespace }}

use MageTech\ImportExport\Import;

class {{ class }} extends Import
{
    public function __construct()
    {
        // Set the model to import into
        // parent::__construct(\App\Models\YourModel::class);

        // Define column mapping
        // $this->map([
        //     'Column Name' => 'model_attribute',
        // ]);

        // Define validation rules
        // $this->validate([
        //     'model_attribute' => ['required', 'email'],
        // ]);
    }
}
STUB;
    }
}
