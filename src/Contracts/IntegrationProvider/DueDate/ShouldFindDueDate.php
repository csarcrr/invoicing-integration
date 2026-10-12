<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePagination;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\DueDateData;
use Illuminate\Support\Collection;

interface ShouldFindDueDate extends ShouldExecute, ShouldHavePagination, ShouldHavePayload
{
    /**
     * @return Collection<int, DueDateData>
     */
    public function getList(): Collection;
}
