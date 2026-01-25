<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

it('can add related document to invoice', function (Provider $provider, string $fixtureName, InvoiceType $type) {
    $data = fixtures()->request()->invoice()->relatedDocument()->files($fixtureName);

    $invoice = Invoice::create();
    $invoice->type($type);
    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->payment(new Payment(amount: 1000, method: PaymentMethod::CREDIT_CARD));
    $invoice->relatedDocument(99999999);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers')->with([
    ['normal_related_document', InvoiceType::Invoice],
    ['normal_related_document', InvoiceType::InvoiceReceipt],
    ['normal_related_document', InvoiceType::InvoiceSimple],
    ['normal_related_document', InvoiceType::Receipt],
]);

it('can add related document to a NC', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->relatedDocument()->files($fixtureName);

    $invoice = Invoice::create();
    $item = new Item(reference: 'reference-1');
    $item->relatedDocument('FT 01P2025/1', 1);

    $invoice->type(InvoiceType::CreditNote);
    $invoice->item($item);
    $invoice->payment(new Payment(amount: 1000, method: PaymentMethod::CREDIT_CARD));
    $invoice->relatedDocument('FT 01P2025/1', 1);
    $invoice->creditNoteReason('Product damaged');

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['nc_related_document']);
