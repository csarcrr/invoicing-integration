<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;

interface ShouldCreatePaymentMethod extends ShouldExecute, ShouldHavePayload
{
    public function getPaymentMethod(): PaymentMethodData;
}
