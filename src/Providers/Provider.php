<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Contracts\HasHandler;
use Exception;

final class Provider
{
    protected mixed $provider;

    protected string $path = '';

    public static function resolve(): Provider
    {
        $instance = new self;

        $instance->setProvider(config('invoicing-integration.provider'));

        return $instance;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;
        $this->path .= 'CsarCrr\\InvoicingIntegration\\Providers\\Builder\\'.$this->provider.'\\';

        return $this;
    }

    public function invoice()
    {
        $this->path .= 'Invoice\\';

        return $this;
    }

    public function product()
    {
        $this->path .= 'Product\\';

        return $this;
    }

    public function create(mixed ...$args)
    {
        $this->path .= 'Create';

        $providerAction = (new ($this->path)());

        $this->ensureHandlerExists($providerAction);

        return $providerAction->handle(...$args);
    }

    protected function ensureHandlerExists(mixed $action): void
    {
        throw_if(
            ! $action instanceof HasHandler,
            Exception::class,
            'The provider action must implement the HasHandler contract to process the action.'
        );
    }
}
