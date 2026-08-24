<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Console;

use Illuminate\Console\GeneratorCommand;

class MakeSortCommand extends GeneratorCommand
{
    protected $signature = 'mts:query:make-sort {name : The name of the sort}';

    protected $description = 'Create a new custom sort class';

    protected $type = 'Sort';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/sort.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\QuerySorts';
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
