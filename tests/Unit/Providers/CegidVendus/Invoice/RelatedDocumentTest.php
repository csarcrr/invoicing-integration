<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new Item;
    $this->client = new Client;
});

it('can set a related document when FT or similar', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->invoice->addRelatedDocument(9999999);
    $this->invoice->addItem($this->item);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('related_document_id'))->toBeInt();
    expect($resolve->payload()->get('related_document_id'))->toBe(9999999);
});

it('does not can set a related document when not a number in FT or similar', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->invoice->addRelatedDocument('abc');
    $this->invoice->addItem($this->item);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('related_document_id'))->toBeNull();
});

it('can set a related document when RG', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $payment = new Payment;
    $payment->setAmount(500);
    $payment->setMethod(PaymentMethod::CREDIT_CARD);

    $this->invoice->addRelatedDocument('FT 01P2025/1');
    $this->invoice->addItem($this->item);
    $this->invoice->setType(InvoiceType::Receipt);
    $this->invoice->addPayment($payment);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('invoices')->first()->get('document_number'))
        ->toBe('FT 01P2025/1');
});
