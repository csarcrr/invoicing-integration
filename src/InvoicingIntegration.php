<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresItemsException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceTypeIsNotSetException;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceTransportDetails;
use Illuminate\Support\Collection;

class InvoicingIntegration
{
    protected ?InvoiceClient $client = null;

    protected ?DocumentType $type = null;

    protected ?InvoiceTransportDetails $transport = null;

    protected Carbon $date;

    protected Carbon $dateDue;

    protected Collection $payments;

    protected Collection $items;

    protected Collection $relatedDocuments;

    public function __construct(
        protected string $provider
    ) {
        $this->items = collect();
        $this->payments = collect();
        $this->relatedDocuments = collect();
        $this->date = Carbon::now();
        $this->type = DocumentType::Invoice;
    }

    public function create()
    {
        return $this;
    }

    public function client(): ?InvoiceClient
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

    public function relatedDocuments(): Collection
    {
        return $this->relatedDocuments;
    }

    public function type(): DocumentType
    {
        return $this->type;
    }

    public function date(): Carbon
    {
        return $this->date;
    }

    public function dateDue(): Carbon
    {
        return $this->dateDue;
    }

    public function transport(): ?InvoiceTransportDetails
    {
        return $this->transport;
    }

    public function setTransport(?InvoiceTransportDetails $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function setClient(InvoiceClient $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function addItem(InvoiceItem $item): self
    {
        $this->items->push($item);

        return $this;
    }

    public function addPayment(InvoicePayment $payment): self
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

    public function setDateDue(Carbon $dateDue): self
    {
        $this->dateDue = $dateDue;

        return $this;
    }

    public function addRelatedDocument(string $relatedDocument): self
    {
        $this->relatedDocuments->push($relatedDocument);

        return $this;
    }

    public function invoice(): InvoiceData
    {
        $this->ensureHasItems();
        $this->ensureTypeIsSet();
        $this->ensureClientHasNeededDetails();

        $resolve = app($this->provider)->type($this->type());

        if ($this->items()->isNotEmpty()) {
            $resolve->items($this->items());
        }

        if ($this->client()) {
            $resolve->client($this->client());
        }

        if ($this->payments()->isNotEmpty()) {
            $resolve->payments($this->payments());
        }

        if ($this->relatedDocuments()->isNotEmpty()) {
            $resolve->relatedDocuments($this->relatedDocuments());
        }

        $resolve->send();

        return $resolve->invoice();
    }

    protected function ensureTypeIsSet(): void
    {
        throw_if(is_null($this->type), InvoiceTypeIsNotSetException::class);
    }

    protected function ensureHasItems(): void
    {
        if ($this->type() === DocumentType::Receipt) {
            return;
        }

        throw_if($this->items->isEmpty(), InvoiceRequiresItemsException::class);
    }

    protected function ensureClientHasNeededDetails()
    {
        if (! $this->client) {
            return;
        }

        throw_if(
            ! is_null($this->client->vat) && empty($this->client->vat),
            InvoiceRequiresClientVatException::class
        );

        throw_if(
            $this->client->name && ! $this->client->vat,
            InvoiceRequiresVatWhenClientHasName::class
        );
    }
}
