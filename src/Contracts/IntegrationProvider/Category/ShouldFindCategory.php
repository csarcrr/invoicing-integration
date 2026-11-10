<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePagination;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use Illuminate\Support\Collection;

interface ShouldFindCategory extends ShouldExecute, ShouldHavePagination, ShouldHavePayload
{
    /**
     * @return Collection<int, CategoryData>
     */
    public function getList(): Collection;
}
