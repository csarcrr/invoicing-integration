<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType as EnumsInvoiceType;
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

it('assigns due date properly', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $this->invoice->addItem($this->item);
    $this->invoice->setDueDate(Carbon::now()->addDays(15));

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('date_due'))
        ->toBe(Carbon::now()->addDays(15)->toDateString());
});

it('fails when due date is assigned not to a FT invoice', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);
    $payment = new Payment(amount: 500, method: PaymentMethod::MB);

    $this->invoice->addItem($this->item);
    $this->invoice->addPayment($payment);
    $this->invoice->setType(EnumsInvoiceType::InvoiceReceipt);
    $this->invoice->setDueDate(Carbon::now()->addDays(15));

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('date_due'))->toBe(Carbon::now()->addDays(15)->toDateString());
})->throws(Exception::class, 'Due date can only be set for Invoice document types.');
