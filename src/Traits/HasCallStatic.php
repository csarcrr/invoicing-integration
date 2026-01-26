<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

trait HasCallStatic
{
    public static function __callStatic(string $method, array $arguments): static
    {
        return (new static)->$method(...$arguments);
    }

    public function __call(string $method, array $arguments): static
    {
        if (! method_exists($this, $method)) {
            throw new \BadMethodCallException(
                sprintf('Method %s::%s does not exist.', static::class, $method)
            );
        }

        return $this->$method(...$arguments);
    }
}
