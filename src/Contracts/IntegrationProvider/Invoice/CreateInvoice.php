<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use CsarCrr\InvoicingIntegration\ValueObjects\TransportDetails;
use Illuminate\Support\Collection;

interface CreateInvoice
{
    public function execute(): Invoice;

    public function client(Client $client): self;

    public function item(Item $item): self;

    public function payment(Payment $payments): self;

    public function transport(TransportDetails $transport): self;

    public function type(InvoiceType $type): self;

    public function dueDate(Carbon $dueDate): self;

    public function outputFormat(OutputFormat $outputFormat): self;

    public function relatedDocument(int|string $relatedDocument, ?int $row = null): self;

    public function creditNoteReason(string $reason): self;

    public function notes(string $notes): self;

    public function getClient(): ?Client;

    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection;

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection;

    public function getTransport(): ?TransportDetails;

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
