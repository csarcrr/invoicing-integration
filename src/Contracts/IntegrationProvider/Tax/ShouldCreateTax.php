<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\TaxData;

interface ShouldCreateTax extends ShouldExecute, ShouldHavePayload
{
    public function execute(): self;

    public function getTax(): TaxData;
}
