<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Company;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Data\CompanyData;
use Illuminate\Support\Collection;

interface ShouldFindCompany extends ShouldExecute
{
    public function execute(): self;

    /**
     * @return Collection<int, CompanyData>
     */
    public function getList(): Collection;
}
