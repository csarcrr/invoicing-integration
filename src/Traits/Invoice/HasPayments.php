<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use Illuminate\Support\Collection;

trait HasPayments
{
    protected Collection $payments;

    public function payment(Payment $payment): self
    {
        $this->payments->push($payment);

        return $this;
    }

    public function getPayments(): Collection
    {
        return $this->payments;
    }
}