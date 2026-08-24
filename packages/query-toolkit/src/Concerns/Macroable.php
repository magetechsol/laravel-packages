<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Concerns;

use Closure;

trait Macroable
{
    protected static array $macros = [];

    public static function macro(string $name, Closure $callback): void
    {
        static::$macros[$name] = $callback;
    }

    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    public static function getMacro(string $name): ?Closure
    {
        return static::$macros[$name] ?? null;
    }

    public function __call(string $method, array $parameters)
    {
        if (static::hasMacro($method)) {
            $macro = static::getMacro($method);

            return $macro->bindTo($this, static::class)($this, ...$parameters);
        }

        if (method_exists($this->query, $method)) {
            $this->query = $this->query->{$method}(...$parameters);

            return $this;
        }

        throw new \BadMethodCallException("Method [{$method}] does not exist on " . static::class);
    }
}
