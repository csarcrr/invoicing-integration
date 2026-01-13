<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts;

use Illuminate\Support\Collection;

interface ShouldHavePayload
{
    public function payload(array|Collection $payload): self;

    public function getPayload(): Collection;
}
