<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use CsarCrr\InvoicingIntegration\ValueObjects\InvoiceData;
use CsarCrr\InvoicingIntegration\ValueObjects\ItemData;
use CsarCrr\InvoicingIntegration\ValueObjects\PaymentData;
use CsarCrr\InvoicingIntegration\ValueObjects\TransportData;
use Illuminate\Support\Collection;

interface CreateInvoice
{
    public function execute(): InvoiceData;

    public function client(ClientData $client): self;

    public function item(ItemData $item): self;

    public function payment(PaymentData $payments): self;

    public function transport(TransportData $transport): self;

    public function type(InvoiceType $type): self;

    public function dueDate(Carbon $dueDate): self;

    public function outputFormat(OutputFormat $outputFormat): self;

    public function relatedDocument(int|string $relatedDocument, ?int $row = null): self;

    public function creditNoteReason(string $reason): self;

    public function notes(string $notes): self;

    public function getClient(): ?ClientData;

    /**
     * @return Collection<int, ItemData>
     */
    public function getItems(): Collection;

    /**
     * @return Collection<int, PaymentData>
     */
    public function getPayments(): Collection;

    public function getTransport(): ?TransportData;

    public function getOutputFormat(): OutputFormat;

    public function getType(): InvoiceType;

    public function getRelatedDocument(): int|string|null;

    public function getCreditNoteReason(): ?string;

    public function getNotes(): ?string;

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection;
}
