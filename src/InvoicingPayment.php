<?php

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;

class InvoicingPayment
{
    public function __construct(
        public DocumentPaymentMethod $method,
        public int $amount
    ) {}
}
