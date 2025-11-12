<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Vendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Vendus\RequestFailedException;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use CsarCrr\InvoicingIntegration\InvoiceItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Vendus
{
    protected ?InvoiceClient $client = null;

    protected DocumentType $type = DocumentType::Invoice;

    protected InvoiceData $invoice;

    protected Collection $items;

    protected Collection $payments;

    protected Collection $relatedDocuments;

    protected Collection $data;

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
        $this->generateInvoice($this->request());

        return $this;
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
        return $this->invoice;
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

        $this->ensureNoEmptyItemsArray();
    }

    public function payload(): Collection
    {
        return $this->data;
    }

    protected function generateInvoice(array $data): void
    {
        $invoice = new InvoiceData;

        if ($data['number'] ?? false) {
            $invoice->setSequence($data['number']);
        }

        $this->invoice = $invoice;
    }

    protected function request()
    {
        $request = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post(
            'https://www.vendus.pt/ws/v1.1/documents/',
            $this->payload()->toArray()
        );

        if (! in_array($request->status(), [200, 201, 300, 301])) {
            $this->throwErrors($request->json());
        }

        return $request->json();
    }

    protected function throwErrors(array $errors)
    {
        $messages = collect($errors['errors'] ?? [])->map(function ($error) {
            return $error['message'] ? $error['code'] . ' - ' . $error['message'] : 'Unknown error';
        })->toArray();

        throw_if(! empty($messages), RequestFailedException::class, implode('; ', $messages));
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
        throw_if(
            $this->type === DocumentType::Receipt && $this->payments->isEmpty(),
            MissingPaymentWhenIssuingReceiptException::class,
        );

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
            ! ($item instanceof InvoiceItem),
            InvoiceItemIsNotValidException::class,
            'The item is not a valid InvoiceItem instance.'
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

        $this->relatedDocuments->each(function (string $id) {
            $this->data->get('invoices')->push(collect(['document_number' => (string) $id]));
        });
    }

    protected function ensureNoEmptyItemsArray()
    {
        $this->data = $this->payload()->filter(function (mixed $value) {
            if ($value instanceof Collection) {
                return $value->isNotEmpty();
            }

            return ! is_null($value);
        });
    }

    private function guardAgainstMissingPaymentConfig(): void
    {
        foreach ($this->options->get('payments') as $key => $value) {
            if (! is_null($value)) {
                return;
            }
        }

        throw new \Exception('The provider configuration is missing payment method details.');
    }
}
