<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Spatie\LaravelData\Data;

trait HasClient
{
    protected ?Data $client = null;

    public function client(Data $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ?Data
    {
        return $this->client;
    }
}
