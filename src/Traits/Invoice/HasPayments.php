<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use CsarCrr\InvoicingIntegration\ValueObjects\PaymentData;
use Illuminate\Support\Collection;

trait HasPayments
{
    /**
     * @var Collection<int, PaymentData>
     */
    protected Collection $payments;

    public function payment(PaymentData $payment): self
    {
        $this->payments->push($payment);

        return $this;
    }

    /**
     * @return Collection<int, PaymentData>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }
}
