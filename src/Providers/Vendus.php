<?php

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\InvoiceData;
use CsarCrr\InvoicingIntegration\InvoicingClient;
use CsarCrr\InvoicingIntegration\InvoicingItem;
use CsarCrr\InvoicingIntegration\InvoicingPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Vendus
{
    protected ?InvoicingClient $client = null;
    protected DocumentType $type = DocumentType::Invoice;
    protected InvoiceData $invoiceData;
    protected Collection $items;
    protected Collection $payments;
    protected Collection $relatedDocuments;

    protected Collection $data;
    protected ?string $sequenceNumber = null;

    public function __construct(
        protected string $apiKey,
        protected string $mode,
        protected Collection $options
    ) {
        $this->data = collect([
            'register_id' => null,
            'type' => null,
            'items' => collect(),
            'payments' => collect(),
            'invoices' => collect(),
        ]);

        $this->payments = collect();
        $this->items = collect();
        $this->relatedDocuments = collect();
    }

    public function send(): self
    {
        $this->buildPayload();
        $this->generateInvoiceData($this->request());

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

    public function payments(InvoicingPayment $payment): self
    {
        $this->payments->push($payment);

        return $this;
    }

    public function relatedDocuments(Collection $relatedDocuments): self
    {
        $this->relatedDocuments = $relatedDocuments;

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
        $this->ensurePaymentsFormat();
        $this->ensureRelatedDocumentsFormat();
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
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post(
            'https://www.vendus.pt/ws/v1.1/documents/',
            $this->payload()->toArray()
        );

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
        if ($this->type === DocumentType::Receipt) {
            return;
        }

        throw_if(
            $this->items->isEmpty(),
            InvoiceItemIsNotValidException::class,
            'The invoice must have at least one item.'
        );

        foreach ($this->items as $item) {
            $this->ensureItemIsValid($item);

            $data = [
                'reference' => $item->reference,
                'qty' => $item->quantity,
            ];

            if ($item->price()) {
                $data['gross_price'] = (float) ($item->price() / 100);
            }

            $this->data->get('items')->push($data);
        }
    }

    protected function ensurePaymentsFormat(): void
    {
        if ($this->payments->isEmpty()) {
            return;
        }

        $this->guardAgainstMissingPaymentConfig();

        foreach ($this->payments as $payment) {
            $data = [
                'amount' => (float) ($payment->amount() / 100),
                'id' => $this->options->get('payments')[$payment->method()->value],
            ];

            $this->data->get('payments')->push($data);
        }
    }

    protected function ensureItemIsValid($item): void
    {
        throw_if(
            ! ($item instanceof InvoicingItem),
            InvoiceItemIsNotValidException::class,
            'The item is not a valid InvoicingItem instance.'
        );
    }

    protected function ensureRelatedDocumentsFormat(): void
    {
        if ($this->type !== DocumentType::Receipt) {
            return;
        }

        throw_if(
            $this->relatedDocuments->isEmpty(),
            InvoiceItemIsNotValidException::class,
            'The receipt must have at least one related document.'
        );

        $this->relatedDocuments = $this->relatedDocuments
            ->map(fn($id) => (int) $id);

        $this->data->put('invoices', $this->relatedDocuments);
    }

    private function guardAgainstMissingPaymentConfig(): void
    {
        foreach ($this->options->get('payments') as $key => $value) {
            if (!is_null($value)) {
                return;
            }
        }

        throw new \Exception('The provider configuration is missing payment method details.');
    }
}
