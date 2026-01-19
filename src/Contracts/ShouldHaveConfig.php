<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts;

use Illuminate\Support\Collection;

interface ShouldHaveConfig
{
    /**
     * @param  array<string, mixed>|Collection<string, mixed>  $config
     */
    public function config(array|Collection $config): self;

    /**
     * @return Collection<string, mixed>
     */
    public function getConfig(): Collection;
}
