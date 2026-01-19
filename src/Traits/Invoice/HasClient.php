<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject;

trait HasClient
{
    protected ?ClientDataObject $client = null;

    public function client(ClientDataObject $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ?ClientDataObject
    {
        return $this->client;
    }
}
