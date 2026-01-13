<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

use Illuminate\Support\Collection;

trait HasConfig
{
    protected Collection $providerOptions;

    public function config(array|Collection $config): self
    {
        if (is_array($config)) {
            $config = collect($config);
        }

        $this->providerOptions = $config;

        return $this;
    }

    public function getConfig(): Collection
    {
        return $this->providerOptions;
    }
}
