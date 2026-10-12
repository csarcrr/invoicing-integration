<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePagination;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use Illuminate\Support\Collection;

interface ShouldFindPaymentMethod extends ShouldExecute, ShouldHavePagination, ShouldHavePayload
{
    /**
     * @return Collection<int, PaymentMethodData>
     */
    public function getList(): Collection;
}
