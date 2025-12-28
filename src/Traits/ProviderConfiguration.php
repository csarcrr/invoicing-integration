<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

trait ProviderConfiguration
{
    protected Collection $providerOptions;

    protected function config(array|Collection $config): void
    {
        if(is_array($config)) {
            $config = collect($config);
        }

        $this->providerOptions = $config;
    }

    protected function getConfig(): Collection
    {
        return $this->providerOptions;
    }
}
