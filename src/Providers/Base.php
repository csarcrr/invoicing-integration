<?php 
namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceTransportDetails;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use Illuminate\Support\Collection;

abstract class Base {
    protected ?InvoiceClient $client = null;
    protected InvoiceData $invoice;
    protected ?InvoiceTransportDetails $transportDetails = null;

    protected Collection $items;
    protected Collection $payments;
    protected Collection $relatedDocuments;
    protected Collection $data;

    protected DocumentType $type = DocumentType::Invoice;

    public function payload(): Collection
    {
        return $this->data;
    }

    public function client(InvoiceClient $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function items(Collection $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function payments(Collection $payments): self
    {
        $this->payments = $payments;

        return $this;
    }

    public function relatedDocuments(Collection $relatedDocuments): self
    {
        $this->relatedDocuments = $relatedDocuments;

        return $this;
    }

    public function invoice()
    {
        $this->generateInvoice($this->request());

        return $this->invoice;
    }

    public function type(DocumentType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function transportDetails(InvoiceTransportDetails $transportDetails): self
    {
        $this->transportDetails = $transportDetails;

        return $this;
    }

    abstract protected function generateInvoice(array $data): void;
    abstract protected function request () : array;
    abstract protected function throwErrors (array $errors) : void;
}