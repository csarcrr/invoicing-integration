<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('transforms to provider payload with single payment', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->payment()->files($fixtureName);

    $invoice = Invoice::create(
        InvoiceData::make([
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'payments' => [
                PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]),
            ],
        ])
    );

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['payment']);

it('transforms to provider payload with multiple payments', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->payment()->files($fixtureName);

    $invoice = Invoice::create(
        InvoiceData::make([
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'payments' => [
                PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]),
                PaymentData::from(['amount' => 500, 'method' => PaymentMethod::MONEY]),
            ],
        ])
    );

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['payment_multiple']);

it('throws error when configuration is not set', function (Provider $provider) {
    config()->set('invoicing-integration.providers.'.Provider::CEGID_VENDUS->value.'.payments', [
        PaymentMethod::CREDIT_CARD->value => null,
        PaymentMethod::MONEY->value => null,
        PaymentMethod::MB->value => null,
        PaymentMethod::MONEY_TRANSFER->value => null,
        PaymentMethod::CURRENT_ACCOUNT->value => null,
    ]);

    $invoice = Invoice::create(
        InvoiceData::make([
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'payments' => [PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD])],
        ])
    );

    $invoice->getPayload();
})->with('providers')->throws(Exception::class, 'Payment method not configured.');
