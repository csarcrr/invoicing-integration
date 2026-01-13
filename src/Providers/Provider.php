<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Contracts\HasHandler;
use Exception;
use Throwable;

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

    public function invoice(): self
    {
        $this->path .= 'Invoice\\';

        return $this;
    }

    public function product(): self
    {
        $this->path .= 'Product\\';

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function create(mixed ...$args): HasHandler
    {
        $this->path .= 'Create';

        /** @var HasHandler $providerAction */
        $providerAction = (new ($this->path)());

        $this->ensureHandlerExists($providerAction);

        return $providerAction->handle(...$args);
    }

    /**
     * @throws Throwable
     */
    protected function ensureHandlerExists(mixed $action): void
    {
        throw_if(
            ! $action instanceof HasHandler,
            Exception::class,
            'The provider action must implement the HasHandler contract to process the action.'
        );
    }
}
