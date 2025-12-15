<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoicePayment;

it('can set a related document when FT or similar', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $invoicing = Invoice::create();
    $invoicing->addRelatedDocument('FT 01P2025/1');
    $invoicing->addItem($item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('related_document_id'))->toBe('FT 01P2025/1');
});

it('can set a related document when RG', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new InvoicePayment;
    $payment->setAmount(500);
    $payment->setMethod(DocumentPaymentMethod::CREDIT_CARD);

    $invoicing = Invoice::create();
    $invoicing->addRelatedDocument('FT 01P2025/1');
    $invoicing->addItem($item);
    $invoicing->setType(DocumentType::Receipt);
    $invoicing->addPayment($payment);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('invoices')->first()->get('document_number'))
        ->toBe('FT 01P2025/1');
});
