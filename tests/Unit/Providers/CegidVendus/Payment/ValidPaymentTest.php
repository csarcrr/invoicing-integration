<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem;
    $this->client = new InvoiceClient;
});

it('has a valid payment payload', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $payment = new InvoicePayment(amount: 500, method: DocumentPaymentMethod::MB);

    $this->invoice->addItem($this->item);
    $this->invoice->addPayment($payment);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('payments'))
        ->toBeInstanceOf(Collection::class);
    expect($resolve->payload()->get('payments')->first()['amount'])->toBe(5.0);
    expect($resolve->payload()->get('payments')->first()['id'])->toBe(19999);
});
