<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Console;

use Illuminate\Console\GeneratorCommand;

class MakeSearchCommand extends GeneratorCommand
{
    protected $signature = 'mts:query:make-search {name : The name of the search}';

    protected $description = 'Create a new custom search class';

    protected $type = 'Search';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/search.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\QuerySearches';
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
