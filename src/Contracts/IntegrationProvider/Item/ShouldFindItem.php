<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePagination;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use Illuminate\Support\Collection;

interface ShouldFindItem extends ShouldExecute, ShouldHavePagination, ShouldHavePayload
{
    /**
     * @return Collection<int, mixed>
     */
    public function get(): Collection;
}
