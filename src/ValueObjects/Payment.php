<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Enums\InvoicePaymentMethod;

class Payment
{
    public function __construct(
        protected ?InvoicePaymentMethod $method = null,
        protected ?int $amount = null
    ) {}

    public function amount(): ?int
    {
        return $this->amount;
    }

    public function method(): ?InvoicePaymentMethod
    {
        return $this->method;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function setMethod(InvoicePaymentMethod $method): self
    {
        $this->method = $method;

        return $this;
    }
}
