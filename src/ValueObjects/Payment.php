<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;

class Payment
{
    public function __construct(
        protected ?PaymentMethod $method = null,
        protected ?int $amount = null
    ) {}

    public function amount(): ?int
    {
        return $this->amount;
    }

    public function method(): ?PaymentMethod
    {
        return $this->method;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function setMethod(PaymentMethod $method): self
    {
        $this->method = $method;

        return $this;
    }
}
