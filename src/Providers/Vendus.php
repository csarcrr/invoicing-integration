<?php

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\InvoiceData;
use CsarCrr\InvoicingIntegration\InvoicingClient;
use CsarCrr\InvoicingIntegration\InvoicingItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Vendus
{
    protected ?InvoicingClient $client = null;

    protected Collection $items;

    protected ?string $sequenceNumber = null;

    protected InvoiceData $invoiceData;

    protected DocumentType $type = DocumentType::Fatura;

    protected Collection $data;

    public function __construct(
        protected string $apiKey,
        protected string $mode
    ) {
        $this->data = collect([
            'register_id' => null,
            'type' => null,
        ]);
    }

    public function send(): self
    {
        $this->buildPayload();

        $request = $this->request();

        $this->generateInvoiceData($request);

        return $this;
    }

    public function client(InvoicingClient $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function items(Collection $items): self
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

    public function buildPayload(): void
    {
        $this->setDocumentType();
        $this->ensureClientFormat();
        $this->ensureItemsFormat();
    }

    public function payload(): Collection
    {
        return $this->data;
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
        ])->post('https://www.vendus.pt/ws/v1.1/documents/', $this->payload()->toArray());

        return $request->json();
    }

    protected function setDocumentType()
    {
        $this->data->put('type', $this->type->value);
    }

    protected function ensureClientFormat(): void
    {
        if (! $this->client) {
            return;
        }

        $this->data->put('client', [
            'name' => $this->client->name,
            'fiscal_id' => $this->client->vat,
        ]);
    }

    protected function ensureItemsFormat(): void
    {
        foreach ($this->items as $item) {
            $this->ensureItemIsValid($item);

            $data = [
                'reference' => $item->reference,
                'qty' => $item->quantity,
            ];

            if ($item->price) {
                $data['gross_price'] = (float) ($item->price / 100);
            }

            $this->data->put('items', $this->data->get('items', collect())->push($data));
        }
    }

    protected function ensureItemIsValid($item): void
    {
        throw_if(! ($item instanceof InvoicingItem), InvoiceItemIsNotValidException::class, 'The item is not a valid InvoicingItem instance.');
    }
}
