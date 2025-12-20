<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;
use CsarCrr\InvoicingIntegration\InvoicePayment;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem;
    $this->client = new InvoiceClient;
});

it('fails when no payment id is configured', function () {
    config()->set('invoicing-integration.providers.cegid_vendus.config.payments', [
        DocumentPaymentMethod::MB->value => null,
        DocumentPaymentMethod::CREDIT_CARD->value => null,
        DocumentPaymentMethod::CURRENT_ACCOUNT->value => null,
        DocumentPaymentMethod::MONEY->value => null,
        DocumentPaymentMethod::MONEY_TRANSFER->value => null,
    ]);

    $this->item->setReference('reference-1');
    $this->item->setPrice(500);

    $payment = new InvoicePayment(amount: 500, method: DocumentPaymentMethod::MB);

    $this->invoice->addItem($this->item);
    $this->invoice->addPayment($payment);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();
})->throws(
    Exception::class,
    'The provider configuration is missing payment method details.'
);
