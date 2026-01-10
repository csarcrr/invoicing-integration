<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts;

use Illuminate\Support\Collection;

interface HasConfig
{
    public function config(array|Collection $config): self;

    public function getConfig(): Collection;
}
