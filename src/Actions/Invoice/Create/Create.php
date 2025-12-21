<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions\Invoice\Create;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Actions\Invoice\Base;
use CsarCrr\InvoicingIntegration\Contracts\HasData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\DueDate\DueDateCannotBeInPastException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresItemsException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\CreditNote\CreditNoteReasonCannotBeSetException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceTypeIsNotSetException;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use CsarCrr\InvoicingIntegration\ValueObjects\TransportDetails;
use Illuminate\Support\Collection;

final class Create extends Base
{
    protected ?Client $client = null;

    protected ?InvoiceType $type = null;

    protected ?TransportDetails $transport = null;

    protected Carbon $date;

    protected ?Carbon $dueDate = null;

    protected Collection $payments;

    protected Collection $items;

    protected Collection $relatedDocuments;

    protected OutputFormat $outputFormat = OutputFormat::PDF_BASE64;

    protected ?string $creditNoteReason = null;

    public function __construct(
        protected ?string $provider = null
    ) {
        $this->items = collect();
        $this->payments = collect();
        $this->relatedDocuments = collect();
        $this->date = Carbon::now();
        $this->type = InvoiceType::Invoice;
    }

    public function execute(): HasData
    {
        $this->ensureHasItems();
        $this->ensureTypeIsSet();
        $this->ensureClientHasNeededDetails();

        return Provider::resolve()->invoice()->create($this)->new();
    }

    public function client(): ?Client
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

    public function type(): InvoiceType
    {
        return $this->type;
    }

    public function date(): Carbon
    {
        return $this->date;
    }

    public function creditNoteReason(): ?string
    {
        return $this->creditNoteReason;
    }

    public function dueDate(): ?Carbon
    {
        return $this->dueDate;
    }

    public function transport(): ?TransportDetails
    {
        return $this->transport;
    }

    public function setTransport(?TransportDetails $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function setCreditNoteReason(string $reason): self
    {
        throw_if(
            $this->type !== InvoiceType::CreditNote,
            CreditNoteReasonCannotBeSetException::class
        );

        $this->creditNoteReason = $reason;

        return $this;
    }

    public function addItem(Item $item): self
    {
        $this->items->push($item);

        return $this;
    }

    public function addPayment(Payment $payment): self
    {
        $this->payments->push($payment);

        return $this;
    }

    public function setType(InvoiceType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setDate(Carbon $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function setDueDate(Carbon $dueDate): self
    {
        throw_if(
            $dueDate->toDateString() < Carbon::now()->toDateString(),
            DueDateCannotBeInPastException::class
        );

        $this->dueDate = $dueDate;

        return $this;
    }

    public function addRelatedDocument(int|string $relatedDocument): self
    {
        $this->relatedDocuments->push($relatedDocument);

        return $this;
    }

    public function get(): self
    {
        return $this;
    }

    public function asEscPos(): self
    {
        $this->outputFormat = OutputFormat::ESCPOS;

        return $this;
    }

    public function outputFormat(): OutputFormat
    {
        return $this->outputFormat;
    }

    protected function ensureTypeIsSet(): void
    {
        throw_if(is_null($this->type), InvoiceTypeIsNotSetException::class);
    }

    protected function ensureHasItems(): void
    {
        if ($this->type() === InvoiceType::Receipt) {
            return;
        }

        throw_if($this->items->isEmpty(), InvoiceRequiresItemsException::class);
    }

    protected function ensureClientHasNeededDetails()
    {
        if (! $this->client) {
            return;
        }
    }
}
