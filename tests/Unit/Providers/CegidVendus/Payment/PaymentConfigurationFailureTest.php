<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\InvoicePaymentMethod;
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

it('fails when no payment id is configured', function () {
    config()->set('invoicing-integration.providers.CegidVendus.config.payments', [
        InvoicePaymentMethod::MB->value => null,
        InvoicePaymentMethod::CREDIT_CARD->value => null,
        InvoicePaymentMethod::CURRENT_ACCOUNT->value => null,
        InvoicePaymentMethod::MONEY->value => null,
        InvoicePaymentMethod::MONEY_TRANSFER->value => null,
    ]);

    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $payment = new Payment(amount: 500, method: InvoicePaymentMethod::MB);

    $this->invoice->addItem($this->item);
    $this->invoice->addPayment($payment);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);
})->throws(
    Exception::class,
    'The provider configuration is missing payment method details.'
);
