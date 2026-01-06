<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use CsarCrr\InvoicingIntegration\ValueObjects\TransportDetails;

trait HasTransport
{
    protected ?TransportDetails $transport = null;

    public function transport(TransportDetails $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function getTransport(): ?TransportDetails
    {
        return $this->transport;
    }
}
