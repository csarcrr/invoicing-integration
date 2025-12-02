<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers;

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\InvoiceTypeDoesNotSupportTransportException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\RequestFailedException;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoicingIntegration;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class CegidVendus extends Base
{

    protected array $invoiceTypesThatRequirePayments = [
        DocumentType::Receipt,
        DocumentType::InvoiceReceipt,
        DocumentType::InvoiceSimple,
    ];
    public function __construct(
        protected string $apiKey,
        protected string $mode,
        protected Collection $options,
        protected InvoicingIntegration $invoicing,
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
        $this->ensureDueDate();

        $this->ensureNoEmptyItemsArray();
    }

    protected function ensureDocumentType()
    {
        $this->data->put('type', $this->invoicing->type()->value);
    }

    protected function ensureClientFormat(): void
    {
        if (! $this->invoicing->client()) {
            return;
        }

        $data = [
            'name' => $this->invoicing->client()->name,
            'fiscal_id' => $this->invoicing->client()->vat,
        ];

        if($this->invoicing->client()->address()) {
            $data['address'] = $this->invoicing->client()->address();
        }

        if($this->invoicing->client()->city()) {
            $data['city'] = $this->invoicing->client()->city();
        }

        if($this->invoicing->client()->postalCode()) {
            $data['postalcode'] = $this->invoicing->client()->postalCode();
        }

        if($this->invoicing->client()->country()) {
            $data['country'] = $this->invoicing->client()->country();
        }

        if($this->invoicing->client()->email()) {
            $data['email'] = $this->invoicing->client()->email();
        }

        if($this->invoicing->client()->phone()) {
            $data['phone'] = $this->invoicing->client()->phone();
        }

        $this->data->put('client', $data);
    }

    protected function ensureItemsFormat(): void
    {
        if ($this->invoicing->type() === DocumentType::Receipt) {
            return;
        }

        throw_if(
            $this->invoicing->items()->isEmpty(),
            InvoiceItemIsNotValidException::class,
            'The invoice must have at least one item.'
        );

        foreach ($this->invoicing->items() as $item) {
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
            in_array(
                $this->invoicing->type(),
                $this->invoiceTypesThatRequirePayments
            ) && $this->invoicing->payments()->isEmpty(),
            MissingPaymentWhenIssuingReceiptException::class,
        );

        if ($this->invoicing->payments()->isEmpty()) {
            return;
        }

        $this->guardAgainstMissingPaymentConfig();

        foreach ($this->invoicing->payments() as $payment) {
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
        if ($this->invoicing->type() !== DocumentType::Receipt) {
            return;
        }

        throw_if(
            $this->invoicing->relatedDocuments()->isEmpty(),
            InvoiceItemIsNotValidException::class,
            'The receipt must have at least one related document.'
        );

        $this->invoicing->relatedDocuments()->each(function (string $id) {
            $this->data->get('invoices')->push(collect(['document_number' => (string) $id]));
        });
    }

    protected function ensureTransportDetailsFormat(): void
    {
        if (! $this->invoicing->transport()) {
            return;
        }

        throw_if(
            ! in_array($this->invoicing->type(), [DocumentType::Invoice, DocumentType::Transport]),
            InvoiceTypeDoesNotSupportTransportException::class
        );

        throw_if(
            is_null($this->invoicing->transport()->origin()->date()),
            NeedsDateToSetLoadPointException::class
        );

        $this->data->get('movement_of_goods')->put('loadpoint', [
            'date' => $this->invoicing->transport()->origin()->date(),
            'time' => $this->invoicing->transport()->origin()->time(),
            'address' => $this->invoicing->transport()->origin()->address(),
            'postalcode' => $this->invoicing->transport()->origin()->postalCode(),
            'city' => $this->invoicing->transport()->origin()->city(),
            'country' => $this->invoicing->transport()->origin()->country(),
        ]);

        $this->data->get('movement_of_goods')->put('landpoint', [
            'date' => $this->invoicing->transport()->destination()->date(),
            'time' => $this->invoicing->transport()->destination()->time(),
            'address' => $this->invoicing->transport()->destination()->address(),
            'postalcode' => $this->invoicing->transport()->destination()->postalCode(),
            'city' => $this->invoicing->transport()->destination()->city(),
            'country' => $this->invoicing->transport()->destination()->country(),
        ]);

        if ($this->invoicing->transport()->vehicleLicensePlate()) {
            $this->data->get('movement_of_goods')
                ->put(
                    'vehicle_id',
                    $this->invoicing->transport()->vehicleLicensePlate()
                );
        }
    }

    protected function ensureDueDate() : void {
        if (! $this->invoicing->dueDate()) {
            return;
        }

        throw_if(
            $this->invoicing->type() !== DocumentType::Invoice,
            Exception::class,
            'Due date can only be set for Invoice document types.'
        );

        $this->data->put('date_due', $this->invoicing->dueDate()->toDateString());
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
