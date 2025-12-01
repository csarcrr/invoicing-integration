<?php

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType as EnumsDocumentType;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use Dom\DocumentType;

it('assigns due date properly', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->dueDate(Carbon::now()->addDays(15));

    $resolve->create();

    expect($resolve->payload()->get('date_due'))->toBe(Carbon::now()->addDays(15)->toDateString());
});

it('fails when due date is assigned not to a FT invoice', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);
    $payment = new InvoicePayment(amount: 500, method: DocumentPaymentMethod::MB);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->dueDate(Carbon::now()->addDays(15))
        ->payments(collect([$payment]))
        ->type(EnumsDocumentType::InvoiceReceipt);

    $resolve->create();

    expect($resolve->payload()->get('date_due'))->toBe(Carbon::now()->addDays(15)->toDateString());
})->throws(Exception::class, 'Due date can only be set for Invoice document types.');
