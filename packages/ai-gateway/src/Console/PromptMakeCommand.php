<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Console;

use Illuminate\Console\GeneratorCommand;

class PromptMakeCommand extends GeneratorCommand
{
    protected $signature = 'mts:ai:make-prompt {name : The name of the prompt}';

    protected $description = 'Create a new AI prompt class';

    protected $type = 'Prompt';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/prompt.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Ai\\Prompts';
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $nameVariable = strtolower($this->argument('name'));

        return str_replace('{{ name }}', $nameVariable, $stub);
    }
}
