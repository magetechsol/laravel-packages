<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Console;

use Illuminate\Console\GeneratorCommand;

class MakeFilterCommand extends GeneratorCommand
{
    protected $signature = 'mts:query:make-filter {name : The name of the filter}';

    protected $description = 'Create a new custom filter class';

    protected $type = 'Filter';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/filter.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\QueryFilters';
    }

    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return str_replace(
            ['{{ class }}', '{{ namespace }}'],
            [$this->getNameInput(), $this->getNamespace()],
            $stub
        );
    }
}
