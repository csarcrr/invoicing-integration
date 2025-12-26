<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\MissingPaymentWhenIssuingReceiptException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new Item;
    $this->client = new Client;
});

it('has a valid payload', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $this->item->setRelatedDocument('FT 01P2025/1', 1);

    $payment = new Payment;
    $payment->setAmount(500);
    $payment->setMethod(PaymentMethod::MB);

    $this->invoice->setType(InvoiceType::CreditNote);
    $this->invoice->addPayment($payment);
    $this->invoice->addItem($this->item);

    $this->invoice->setCreditNoteReason('Product returned by customer');

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

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

    $item2 = new Item;
    $item2->setReference('reference-2');
    $item2->setPrice(300);
    $item2->setRelatedDocument('FT 01P2025/1', 1);

    $this->invoice->addItem($this->item);
    $this->invoice->addItem($item2);

    $this->invoice->setType(InvoiceType::CreditNote);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);
})->throws(
    InvoiceItemIsNotValidException::class,
    'Credit Note items must have a related document set.'
);

it('fails when no payment is set in the credit note', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $this->item->setRelatedDocument('FT 01P2025/1', 1);

    $this->invoice->setType(InvoiceType::CreditNote);
    $this->invoice->addItem($this->item);

    $this->invoice->setCreditNoteReason('Product returned by customer');

    $resolve = Provider::resolve()->invoice()->create($this->invoice);
})->throws(MissingPaymentWhenIssuingReceiptException::class);
