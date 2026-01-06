<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

it('can add related document to invoice', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName,
    InvoiceType $type
) {
    $data = $fixture->request()->invoice()->relatedDocument()->files($fixtureName);

    $invoice->type($type);
    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->payment(new Payment(amount: 1000, method: PaymentMethod::CREDIT_CARD));
    $invoice->relatedDocument(99999999);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice')->with([
    ['normal_related_document', InvoiceType::Invoice],
    ['normal_related_document', InvoiceType::InvoiceReceipt],
    ['normal_related_document', InvoiceType::InvoiceSimple],
    ['normal_related_document', InvoiceType::Receipt],
]);

it('can add related document to a NC', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName
) {
    $data = $fixture->request()->invoice()->relatedDocument()->files($fixtureName);

    $invoice->type(InvoiceType::CreditNote);
    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->payment(new Payment(amount: 1000, method: PaymentMethod::CREDIT_CARD));
    $invoice->relatedDocument('FT 01P2025/1', 1);
    $invoice->creditNoteReason('Product damaged');

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['nc_related_document']);
