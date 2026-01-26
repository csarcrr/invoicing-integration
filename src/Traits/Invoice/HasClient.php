<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;

trait HasClient
{
    protected ?ClientData $client = null;

    public function client(ClientData $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ?ClientData
    {
        return $this->client;
    }
}
