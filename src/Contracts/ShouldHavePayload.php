<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts;

use Illuminate\Support\Collection;

interface ShouldHavePayload
{
    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection;
}
