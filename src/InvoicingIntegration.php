<?php

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Services\Vendus;

class InvoicingIntegration
{
    protected InvoicingClient $client;

    protected array $items = [];

    public function __construct(
        protected string $key,
        protected string $mode
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
        // Logic to add item to the invoice would go here
        $this->items[] = $item;

        return $this;
    }

    public function invoice()
    {
        $request = new Vendus($this->key, $this->client, $this->items);
        $request->send();

        return $this;
    }
}
