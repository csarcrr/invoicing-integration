<?php

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType as EnumsDocumentType;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use CsarCrr\InvoicingIntegration\InvoicePayment;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem();
    $this->client = new InvoiceClient();
});

it('assigns due date properly', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->invoice->addItem($this->item);
    $this->invoice->setDueDate(Carbon::now()->addDays(15));

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('date_due'))
        ->toBe(Carbon::now()->addDays(15)->toDateString());
});

it('fails when due date is assigned not to a FT invoice', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $payment = new InvoicePayment(amount: 500, method: DocumentPaymentMethod::MB);

    $this->invoice->addItem($this->item);
    $this->invoice->addPayment($payment);
    $this->invoice->setType(EnumsDocumentType::InvoiceReceipt);
    $this->invoice->setDueDate(Carbon::now()->addDays(15));

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('date_due'))->toBe(Carbon::now()->addDays(15)->toDateString());
})->throws(Exception::class, 'Due date can only be set for Invoice document types.');
