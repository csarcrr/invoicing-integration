<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new Item;
    $this->client = new Client;
});

it('has a valid payment payload', function () {
    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $payment = new Payment(amount: 500, method: PaymentMethod::MB);

    $this->invoice->addItem($this->item);
    $this->invoice->addPayment($payment);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    expect($resolve->payload()->get('payments'))
        ->toBeInstanceOf(Collection::class);
    expect($resolve->payload()->get('payments')->first()['amount'])->toBe(5.0);
    expect($resolve->payload()->get('payments')->first()['id'])->toBe(19999);
});
