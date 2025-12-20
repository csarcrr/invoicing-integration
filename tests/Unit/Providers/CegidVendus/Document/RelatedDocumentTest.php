<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use CsarCrr\InvoicingIntegration\InvoicePayment;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem;
    $this->client = new InvoiceClient;
});

it('can set a related document when FT or similar', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->invoice->addRelatedDocument(9999999);
    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('related_document_id'))->toBeInt();
    expect($resolve->payload()->get('related_document_id'))->toBe(9999999);
});

it('does not can set a related document when not a number in FT or similar', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->invoice->addRelatedDocument('abc');
    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('related_document_id'))->toBeNull();
});

it('can set a related document when RG', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $payment = new InvoicePayment;
    $payment->setAmount(500);
    $payment->setMethod(DocumentPaymentMethod::CREDIT_CARD);

    $this->invoice->addRelatedDocument('FT 01P2025/1');
    $this->invoice->addItem($this->item);
    $this->invoice->setType(DocumentType::Receipt);
    $this->invoice->addPayment($payment);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('invoices')->first()->get('document_number'))
        ->toBe('FT 01P2025/1');
});
