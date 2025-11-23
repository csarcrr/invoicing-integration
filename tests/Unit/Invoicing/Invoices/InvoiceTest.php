<?php

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use Illuminate\Support\Facades\Http;

it('can set a client', function () {
    $client = new InvoiceClient(999999999, 'Client Name');

    $invoice = Invoice::create();
    $invoice->setClient($client);

    expect($invoice->client())->toBeInstanceOf(InvoiceClient::class);
});


it('can set an item', function () {
    $item = new InvoiceItem('reference-1');

    $invoice = Invoice::create();
    $invoice->addItem($item);

    expect($invoice->items()->first())->toBeInstanceOf(InvoiceItem::class);
    expect($invoice->items())->toContain($item);
});

it('can add multiple items', function () {
    $item1 = new InvoiceItem('reference-1');
    $item2 = new InvoiceItem('reference-2');
    $item3 = new InvoiceItem('reference-3');

    $invoice = Invoice::create();
    $invoice->addItem($item1);
    $invoice->addItem($item2);
    $invoice->addItem($item3);

    expect($invoice->items()->first())->toBeInstanceOf(InvoiceItem::class);
    expect($invoice->items())->toContain($item1);
    expect($invoice->items())->toContain($item2);
    expect($invoice->items())->toContain($item3);
});

it('can set a due date on the invoice', function () {
    $dueDate = now()->addDays(30);

    $invoice = Invoice::create();
    $invoice->setDateDue($dueDate);

    expect($invoice->dateDue())->toEqual($dueDate);
});

it('can set a related document', function () {})->todo();
// it('can receive PDF data output', function () {})->todo();
// it('can receive ESCPOS data output', function () {})->todo();
// it('can receive AT CUD QR CODE data', function () {})->todo();
