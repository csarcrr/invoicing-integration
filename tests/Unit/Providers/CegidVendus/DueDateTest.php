<?php

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType as EnumsDocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use Dom\DocumentType;

it('assigns due date properly', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setDueDate(Carbon::now()->addDays(15));

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('date_due'))
        ->toBe(Carbon::now()->addDays(15)->toDateString());
});

it('fails when due date is assigned not to a FT invoice', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);
    $payment = new InvoicePayment(amount: 500, method: DocumentPaymentMethod::MB);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->addPayment($payment);
    $invoicing->setType(EnumsDocumentType::InvoiceReceipt);
    $invoicing->setDueDate(Carbon::now()->addDays(15));

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('date_due'))->toBe(Carbon::now()->addDays(15)->toDateString());
})->throws(Exception::class, 'Due date can only be set for Invoice document types.');
