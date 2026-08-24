<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit;

use Closure;
use MageTech\QueryToolkit\Contracts\IncludeInterface;

class AllowedInclude
{
    public function __construct(
        protected IncludeInterface $include,
    ) {}

    public static function key(string $name, ?string $relationName = null): static
    {
        return new static(new \MageTech\QueryToolkit\Includes\DefaultInclude($name, $relationName));
    }

    public static function count(string $name, ?string $relationName = null): static
    {
        return new static(new \MageTech\QueryToolkit\Includes\CountInclude($name, $relationName));
    }

    public static function custom(string $name, IncludeInterface $include): static
    {
        return new static($include);
    }

    public static function macro(string $name, Closure $callback): static
    {
        return new static(new \MageTech\QueryToolkit\Includes\MacroInclude($name, $callback));
    }

    public function getInclude(): IncludeInterface
    {
        return $this->include;
    }

    public function getName(): string
    {
        return $this->include->getName();
    }

    public function getRelationName(): string
    {
        return $this->include->getRelationName();
    }
}
