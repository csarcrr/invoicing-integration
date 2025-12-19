<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem;
    $this->client = new InvoiceClient;
});

it('has a valid payload', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $this->item->setRelatedDocument('FT 01P2025/1', 1);

    $payment = new InvoicePayment;
    $payment->setAmount(500);
    $payment->setMethod(DocumentPaymentMethod::MB);

    $this->invoice->setType(DocumentType::CreditNote);
    $this->invoice->addPayment($payment);
    $this->invoice->addItem($this->item);

    $this->invoice->setCreditNoteReason('Product returned by customer');

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    $item = $resolve->payload()->get('items')->first();

    expect($resolve->payload()->get('notes'))->toBe('Product returned by customer');
    expect($item['reference_document']['document_number'])->toBe('FT 01P2025/1');
    expect($item['reference_document']['document_row'])->toBe(1);

    expect($resolve->payload()->get('payments'))
        ->toBeInstanceOf(Collection::class);
    expect($resolve->payload()->get('payments')->first()['amount'])->toBe(5.0);
});

it('fails when no related document was set in every item', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $item2 = new InvoiceItem;
    $item2->setReference('reference-2');
    $item2->setPrice(300);
    $item2->setRelatedDocument('FT 01P2025/1', 1);

    $this->invoice->addItem($this->item);
    $this->invoice->addItem($item2);

    $this->invoice->setType(DocumentType::CreditNote);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();
})->throws(
    InvoiceItemIsNotValidException::class,
    'Credit Note items must have a related document set.'
);

it('fails when no payment is set in the credit note', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $this->item->setRelatedDocument('FT 01P2025/1', 1);

    $this->invoice->setType(DocumentType::CreditNote);
    $this->invoice->addItem($this->item);

    $this->invoice->setCreditNoteReason('Product returned by customer');

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();
})->throws(MissingPaymentWhenIssuingReceiptException::class);
