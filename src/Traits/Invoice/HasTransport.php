<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use CsarCrr\InvoicingIntegration\ValueObjects\TransportData;

trait HasTransport
{
    protected ?TransportData $transport = null;

    public function transport(TransportData $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function getTransport(): ?TransportData
    {
        return $this->transport;
    }
}
