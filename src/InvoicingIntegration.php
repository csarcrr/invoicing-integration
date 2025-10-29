<?php

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Enums\DocumentType;

class InvoicingIntegration
{
    protected InvoicingClient $client;

    protected array $items = [];

    protected DocumentType $type;

    public function __construct(
        protected string $provider
    ) {}

    public function create()
    {
        return $this;
    }

    public function forClient(InvoicingClient $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function withItem(InvoicingItem $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    public function asFaturaRecibo(): self
    {
        $this->type = DocumentType::FaturaSimples;

        return $this;
    }

    public function asSimpleInvoice(): self
    {
        $this->type = DocumentType::FaturaSimples;

        return $this;
    }

    public function invoice(): InvoiceData
    {
        $resolve = app($this->provider)
            ->client($this->client)
            ->items($this->items)
            ->type($this->type)
            ->send();

        return $resolve->invoiceData();
    }
}
