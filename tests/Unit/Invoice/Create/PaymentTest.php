<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('can assign a payment', function (Provider $provider) {
    $invoice = Invoice::create();
    $item = ItemData::from(['reference' => 'reference-1']);

    $payment = PaymentData::from([
        'amount' => 500,
        'method' => PaymentMethod::CREDIT_CARD,
    ]);

    $invoice->item($item);
    $invoice->payment($payment);

    expect($invoice->getPayments())->toHaveCount(1);
})->with('providers');

it('can assign multiple payments', function (Provider $provider) {
    $invoice = Invoice::create();
    $item = ItemData::from(['reference' => 'reference-1']);

    $payment1 = PaymentData::from([
        'amount' => 300,
        'method' => PaymentMethod::CREDIT_CARD,
    ]);
    $payment2 = PaymentData::from([
        'amount' => 200,
        'method' => PaymentMethod::MONEY,
    ]);

    $invoice->item($item);
    $invoice->payment($payment1);
    $invoice->payment($payment2);

    expect($invoice->getPayments())->toHaveCount(2);
})->with('providers');

it('transforms to provider payload with single payment', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->payment()->files($fixtureName);

    $invoice = Invoice::create();
    $item = ItemData::from(['reference' => 'reference-1']);
    $payment = PaymentData::from([
        'amount' => 500,
        'method' => PaymentMethod::CREDIT_CARD,
    ]);

    $invoice->item($item);
    $invoice->payment($payment);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['payment']);

it('transforms to provider payload with multiple payments', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->payment()->files($fixtureName);

    $invoice = Invoice::create();
    $item = ItemData::from(['reference' => 'reference-1']);

    $payment = PaymentData::from([
        'method' => PaymentMethod::CREDIT_CARD,
        'amount' => 500,
    ]);

    $payment2 = PaymentData::from([
        'method' => PaymentMethod::MONEY,
        'amount' => 500,
    ]);

    $invoice->item($item);
    $invoice->payment($payment);
    $invoice->payment($payment2);

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

    $invoice = Invoice::create();

    $invoice->item(ItemData::from(['reference' => 'reference-1']));
    $invoice->payment(PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]));

    $invoice->getPayload();
})->with('providers')->throws(Exception::class, 'Payment method not configured.');
