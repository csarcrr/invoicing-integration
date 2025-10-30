<?php

namespace CsarCrr\InvoicingIntegration;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresItemsException;
use Illuminate\Support\Collection;

class InvoicingIntegration
{
    protected InvoicingClient $client;
    protected Collection $items;
    protected DocumentType $type;
    protected Carbon $date;
    protected Collection $payments;

    public function __construct(
        protected string $provider
    ) {
        $this->items = collect();
        $this->payments = collect();
        $this->date = Carbon::now();
    }

    public function create()
    {
        return $this;
    }

    public function client(): InvoicingClient
    {
        return $this->client;
    }

    public function payments(): Collection
    {
        return $this->payments;
    }

    public function items(): Collection
    {
        return $this->items;
    }

    public function type(): DocumentType
    {
        return $this->type;
    }

    public function date(): Carbon
    {
        return $this->date;
    }

    public function setClient(InvoicingClient $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function addItem(InvoicingItem $item): self
    {
        $this->items->push($item);

        return $this;
    }

    public function addPayment(InvoicingPayment $payment): self
    {
        $this->payments->push($payment);

        return $this;
    }

    public function setType(DocumentType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setDate(Carbon $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function invoice(): InvoiceData
    {
        $this->ensureHasItems();

        $resolve = app($this->provider)
            ->client($this->client)
            ->items($this->items)
            ->type($this->type)
            ->send();

        return $resolve->invoiceData();
    }

    protected function ensureHasItems(): void
    {
        throw_if($this->items->isEmpty(), InvoiceRequiresItemsException::class);
    }
}
