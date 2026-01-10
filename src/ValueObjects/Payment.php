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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getMethod(): ?PaymentMethod
    {
        return $this->method;
    }

    public function amount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function method(PaymentMethod $method): self
    {
        $this->method = $method;

        return $this;
    }
}
