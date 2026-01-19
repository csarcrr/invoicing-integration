<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

use Illuminate\Support\Collection;

trait HasConfig
{
    /**
     * @var Collection<string, mixed>
     */
    protected Collection $providerOptions;

    /**
     * @param  array<string, mixed>|Collection<string, mixed>  $config
     */
    public function config(array|Collection $config): self
    {
        if (is_array($config)) {
            $config = collect($config);
        }

        $this->providerOptions = $config;

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getConfig(): Collection
    {
        return $this->providerOptions;
    }
}
