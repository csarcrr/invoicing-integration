<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\RequestFailedException;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class CegidVendus extends Base
{
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
            'movement_of_goods' => collect(),
        ]);

        $this->payments = collect();
        $this->items = collect();
        $this->relatedDocuments = collect();
    }

    public function create(): self
    {
        $this->createPayloadStructure();

        return $this;
    }

    protected function createPayloadStructure(): void
    {
        $this->ensureDocumentType();
        $this->ensureClientFormat();
        $this->ensureItemsFormat();
        $this->ensurePaymentsFormat();
        $this->ensureRelatedDocumentsFormat();
        $this->ensureTransportDetailsFormat();

        $this->ensureNoEmptyItemsArray();
    }

    protected function ensureDocumentType()
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
                'reference' => $item->reference(),
                'qty' => $item->quantity(),
            ];

            $data = $this->buildConditionalItemData($item, $data);

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

    protected function ensureTransportDetailsFormat(): void
    {
        if (! $this->transportDetails) {
            return;
        }

        if (! in_array($this->type, [DocumentType::Invoice, DocumentType::Transport])) {
            return;
        }

        throw_if(
            is_null($this->transportDetails->origin()->date()),
            NeedsDateToSetLoadPointException::class
        );

        $this->data->get('movement_of_goods')->put('loadpoint', [
            'date' => $this->transportDetails->origin()->date(),
            'time' => $this->transportDetails->origin()->time(),
            'address' => $this->transportDetails->origin()->address(),
            'postalcode' => $this->transportDetails->origin()->postalCode(),
            'city' => $this->transportDetails->origin()->city(),
            'country' => $this->transportDetails->origin()->country(),
        ]);

        $this->data->get('movement_of_goods')->put('landpoint', [
            'date' => $this->transportDetails->destination()->date(),
            'time' => $this->transportDetails->destination()->time(),
            'address' => $this->transportDetails->destination()->address(),
            'postalcode' => $this->transportDetails->destination()->postalCode(),
            'city' => $this->transportDetails->destination()->city(),
            'country' => $this->transportDetails->destination()->country(),
        ]);

        if ($this->transportDetails->vehicleLicensePlate()) {
            $this->data->get('movement_of_goods')
                ->put(
                    'vehicle_id',
                    $this->transportDetails->vehicleLicensePlate()
                );
        }
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

    protected function generateInvoice(array $data): void
    {
        $invoice = new InvoiceData;

        if ($data['number'] ?? false) {
            $invoice->setSequence($data['number']);
        }

        $this->invoice = $invoice;
    }

    protected function request(): array
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

    protected function throwErrors(array $errors): void
    {
        $messages = collect($errors['errors'] ?? [])->map(function ($error) {
            return $error['message'] ? $error['code'] . ' - ' . $error['message'] : 'Unknown error';
        })->toArray();

        throw_if(! empty($messages), RequestFailedException::class, implode('; ', $messages));

        throw new Exception('The integration API request failed for an unknown reason.');
    }

    private function buildConditionalItemData(InvoiceItem $item, array $data): array
    {
        if ($item->price()) {
            $data['gross_price'] = (float) ($item->price() / 100);
        }

        if ($item->note()) {
            $data['text'] = $item->note();
        }

        if ($item->type()) {
            $data['type_id'] = $item->type()->vendus();
        }

        if ($item->percentageDiscount()) {
            $data['discount_percentage'] = $item->percentageDiscount();
        }

        if ($item->amountDiscount()) {
            $data['discount_amount'] = (float) $item->amountDiscount() / 100;
        }

        if ($item->tax()) {
            $data['tax_id'] = $item->tax()->vendus();
        }

        if ($item->taxExemption()) {
            $data['tax_exemption'] = $item->taxExemption()->value;

            if ($item->taxExemptionLaw()) {
                $data['tax_exemption_law'] = $item->taxExemptionLaw();
            }
        }

        return $data;
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
