<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceTransportDetails;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use Illuminate\Support\Collection;

abstract class Base
{
    protected ?InvoiceClient $client = null;

    protected InvoiceData $invoice;

    protected ?InvoiceTransportDetails $transportDetails = null;

    protected Collection $items;

    protected Collection $payments;

    protected Collection $relatedDocuments;

    protected Collection $data;

    protected DocumentType $type = DocumentType::Invoice;

    protected ?Carbon $dueDate = null;

    public function payload(): Collection
    {
        return $this->data;
    }

    public function invoice()
    {
        $this->generateInvoice($this->request());

        return $this->invoice;
    }

    abstract protected function generateInvoice(array $data): void;

    abstract protected function request(): array;

    abstract protected function throwErrors(array $errors): void;
}
