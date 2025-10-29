<?php

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\InvoiceData;
use CsarCrr\InvoicingIntegration\InvoicingClient;
use Illuminate\Support\Facades\Http;

class Vendus
{
    public InvoicingClient $client;

    public array $items = [];

    protected InvoiceData $invoiceData;

    protected ?string $sequenceNumber = null;

    protected DocumentType $type = DocumentType::Fatura;

    protected array $data = [
        'register_id' => null,
        'type' => null,
    ];

    public function __construct(
        protected string $apiKey,
        protected string $mode
    ) {}

    public function send(): self
    {
        $this->setupData();

        $data = $this->request();

        $this->generateInvoiceData($data);

        return $this;
    }

    public function client(InvoicingClient $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function items(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function invoiceData()
    {
        return $this->invoiceData;
    }

    public function type(DocumentType $type): self
    {
        $this->type = $type;

        return $this;
    }

    protected function generateInvoiceData(array $data): void
    {
        $invoice = new InvoiceData;

        $invoice->setSequenceNumber($data['number']);

        $this->invoiceData = $invoice;
    }

    protected function request()
    {
        $request = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
        ])->post('https://www.vendus.pt/ws/v1.1/documents/', $this->data);

        return $request->json();
    }

    protected function setupData(): void
    {
        $this->setDocumentType();
        $this->formatClient();
        $this->formatItems();
    }

    protected function setDocumentType()
    {
        $this->data['type'] = $this->type->value;
    }

    protected function formatClient(): void
    {
        $this->data['client'] = [
            'name' => $this->client->name,
            'fiscal_id' => $this->client->vat,
        ];
    }

    protected function formatItems(): void
    {
        foreach ($this->items as $item) {
            if (! ($item instanceof \CsarCrr\InvoicingIntegration\InvoicingItem)) {
                throw new \Exception('Invalid item provided');
            }

            $this->data['items'][] = [
                'reference' => $item->reference,
                'qty' => $item->quantity,
            ];
        }
    }
}
