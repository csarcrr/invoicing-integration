<?php

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;

class InvoicingPayment
{
    public function __construct(
        protected DocumentPaymentMethod $method,
        protected int $amount
    ) {}

    public function amount(): int
    {
        return $this->amount;
    }

    public function method(): DocumentPaymentMethod
    {
        return $this->method;
    }
}
