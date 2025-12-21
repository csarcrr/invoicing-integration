<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers\Builder\CegidVendus\Invoice;

use CsarCrr\InvoicingIntegration\Actions\Invoice\Base;
use CsarCrr\InvoicingIntegration\Actions\Invoice\Create\Create as InvoiceCreate;
use CsarCrr\InvoicingIntegration\Contracts\HasHandler;
use CsarCrr\InvoicingIntegration\ValueObjects\Output;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresVatWhenClientHasName;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\InvoiceTypeDoesNotSupportTransportException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice as InvoiceData;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use Exception;
use Illuminate\Support\Collection;

final class Create extends Invoice implements HasHandler
{
    protected InvoiceCreate $invoicing;

    public function handle(mixed $action): self
    {
        $this->invoicing = $action;

        $this->payload = collect([
            'register_id' => null,
            'type' => null,
            'items' => collect(),
            'payments' => collect(),
            'invoices' => collect(),
            'movement_of_goods' => collect(),
            'output' => 'pdf',
        ]);

        $this->payments = collect();
        $this->items = collect();
        $this->relatedDocuments = collect();

        $this->createPayloadStructure();

        $this->setEndpoint('/documents/');

        return $this;
    }

    public function new (): InvoiceData
    {
        $response = $this->request($this->payload());

        return $this->generate($response);
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
        $this->ensureCreditNoteDetails();

        $this->ensureNoEmptyItemsArray();
    }

    protected function ensureDocumentType()
    {
        $this->payload->put('type', $this->invoicing->type()->value);
    }

    protected function ensureClientFormat(): void
    {
        if (! $this->invoicing->client()) {
            return;
        }

        throw_if(
            ! is_null($this->invoicing->client()->vat) &&
                empty($this->invoicing->client()->vat),
            InvoiceRequiresClientVatException::class
        );

        throw_if(
            $this->invoicing->client()->name &&
                ! $this->invoicing->client()->vat,
            InvoiceRequiresVatWhenClientHasName::class
        );

        $data = [
            'name' => $this->invoicing->client()->name,
            'fiscal_id' => $this->invoicing->client()->vat,
        ];

        if ($this->invoicing->client()->address()) {
            $data['address'] = $this->invoicing->client()->address();
        }

        if ($this->invoicing->client()->city()) {
            $data['city'] = $this->invoicing->client()->city();
        }

        if ($this->invoicing->client()->postalCode()) {
            $data['postalcode'] = $this->invoicing->client()->postalCode();
        }

        if ($this->invoicing->client()->country()) {
            $data['country'] = $this->invoicing->client()->country();
        }

        if ($this->invoicing->client()->email()) {
            $data['email'] = $this->invoicing->client()->email();
        }

        if ($this->invoicing->client()->phone()) {
            $data['phone'] = $this->invoicing->client()->phone();
        }

        if (! is_null($this->invoicing->client()->irsRetention())) {
            $retention = $this->invoicing->client()->irsRetention();
            $data['irs_retention'] = $retention ? 'yes' : 'no';
        }

        $this->payload->put('client', $data);
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

            $this->payload->get('items')->push($data);
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
                'id' => $this->providerOptions()->get('payments')[$payment->method()->value],
            ];

            $this->payload->get('payments')->push($data);
        }
    }

    protected function ensureItemIsValid($item): void
    {
        throw_if(
            ! ($item instanceof Item),
            InvoiceItemIsNotValidException::class,
            'The item is not a valid Item instance.'
        );
    }

    protected function ensureRelatedDocumentsFormat(): void
    {

        $this->handleRelatedDocumentsForReceipts();
        $this->handleRelatedDocumentsForOtherTypes();
    }

    protected function ensureTransportDetailsFormat(): void
    {
        if (! $this->invoicing->transport()) {
            return;
        }

        if (! $this->invoicing->client()) {
            throw new Exception('Client information is required when transport details are provided.');
        }

        throw_if(
            ! in_array($this->invoicing->type(), [DocumentType::Invoice, DocumentType::Transport]),
            InvoiceTypeDoesNotSupportTransportException::class
        );

        throw_if(
            is_null($this->invoicing->transport()->origin()->date()),
            NeedsDateToSetLoadPointException::class
        );

        $this->payload->get('movement_of_goods')->put('loadpoint', [
            'date' => $this->invoicing->transport()->origin()->date()->toDateString(),
            'time' => $this->invoicing->transport()->origin()->time()->format('H:i'),
            'address' => $this->invoicing->transport()->origin()->address(),
            'postalcode' => $this->invoicing->transport()->origin()->postalCode(),
            'city' => $this->invoicing->transport()->origin()->city(),
            'country' => $this->invoicing->transport()->origin()->country(),
        ]);

        $landpointData = [

            'address' => $this->invoicing->transport()->destination()->address(),
            'postalcode' => $this->invoicing->transport()->destination()->postalCode(),
            'city' => $this->invoicing->transport()->destination()->city(),
            'country' => $this->invoicing->transport()->destination()->country(),
        ];

        if ($this->invoicing->transport()->destination()->date()) {
            $landpointData['date'] = $this->invoicing->transport()->destination()->date()->toDateString();
            $landpointData['time'] = $this->invoicing->transport()->destination()->time()->format('H:i');
        }

        $this->payload->get('movement_of_goods')->put('landpoint', $landpointData);

        if ($this->invoicing->transport()->vehicleLicensePlate()) {
            $this->payload->get('movement_of_goods')
                ->put(
                    'vehicle_id',
                    $this->invoicing->transport()->vehicleLicensePlate()
                );
        }
    }

    protected function ensureDueDate(): void
    {
        if (! $this->invoicing->dueDate()) {
            return;
        }

        throw_if(
            $this->invoicing->type() !== DocumentType::Invoice,
            Exception::class,
            'Due date can only be set for Invoice document types.'
        );

        $this->payload->put('date_due', $this->invoicing->dueDate()->toDateString());
    }

    protected function ensureCreditNoteDetails(): void
    {
        if ($this->invoicing->type() !== DocumentType::CreditNote) {
            return;
        }

        if ($this->invoicing->creditNoteReason()) {
            $this->payload->put('notes', $this->invoicing->creditNoteReason());
        }
    }

    protected function ensureNoEmptyItemsArray()
    {
        $this->payload = $this->payload()->filter(function (mixed $value) {
            if ($value instanceof Collection) {
                return $value->isNotEmpty();
            }

            return ! is_null($value);
        });
    }

    protected function generate(array $data): InvoiceData
    {
        $invoice = new InvoiceData();

        if ($data['number'] ?? false) {
            $invoice->setSequence($data['number']);
        }

        if ($data['id'] ?? false) {
            $invoice->setId((int) $data['id']);
        }

        if ($data['atcud'] ?? false) {
            $invoice->setAtcudHash($data['atcud']);
        }

        if ($data['output'] ?? false) {
            $invoice->setOutput(
                new Output(
                    format: $this->invoicing->outputFormat(),
                    content: $data['output'],
                    fileName: $data['number']
                )
            );
        }

        if ($data['amount_gross'] ?? false) {
            $invoice->setTotal((int) ($data['amount_gross'] * 100));
        }

        if ($data['amount_net'] ?? false) {
            $invoice->setTotalNet((int) ($data['amount_net'] * 100));
        }

        return $invoice;
    }

    private function buildConditionalItemData(Item $item, array $data): array
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

        if ($this->invoicing->type() === DocumentType::CreditNote) {
            throw_if(
                $item->relatedDocument()->isEmpty(),
                InvoiceItemIsNotValidException::class,
                'Credit Note items must have a related document set.'
            );

            $data['reference_document'] = [
                'document_number' => $item->relatedDocument()->get('document_id'),
                'document_row' => $item->relatedDocument()->get('row'),
            ];
        }

        return $data;
    }

    private function guardAgainstMissingPaymentConfig(): void
    {
        foreach ($this->providerOptions()->get('payments') as $key => $value) {
            if (! is_null($value)) {
                return;
            }
        }

        throw new \Exception('The provider configuration is missing payment method details.');
    }

    protected function handleRelatedDocumentsForReceipts(): void
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
            $this->payload->get('invoices')->push(collect(['document_number' => (string) $id]));
        });
    }

    protected function handleRelatedDocumentsForOtherTypes(): void
    {
        if (in_array($this->invoicing->type(), [DocumentType::CreditNote, DocumentType::Receipt])) {
            return;
        }

        if ($this->invoicing->relatedDocuments()->isEmpty()) {
            return;
        }

        $value = (int) $this->invoicing->relatedDocuments()[0];

        if ($value <= 0) {
            return;
        }

        $this->payload->put('related_document_id', $value);
    }
}