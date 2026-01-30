<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Action;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\CegidVendus;
use CsarCrr\InvoicingIntegration\ValueObjects\ItemData;
use CsarCrr\InvoicingIntegration\ValueObjects\PaymentData;

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

it('has expected payload', function (Provider $provider, string $fixtureName) {
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

it('has expected payload with multiple payments', function (Provider $provider, string $fixtureName) {
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

it('throws error when configuration is not set', function () {
    config()->set('invoicing-integration.providers.'.Provider::CEGID_VENDUS->value.'.config', [
        'payments' => [
            PaymentMethod::CREDIT_CARD->value => null,
            PaymentMethod::MONEY->value => null,
            PaymentMethod::MB->value => null,
            PaymentMethod::MONEY_TRANSFER->value => null,
            PaymentMethod::CURRENT_ACCOUNT->value => null,
        ],
    ]);

    $invoice = CegidVendus::invoice(Action::CREATE);
    $item = ItemData::from(['reference' => 'reference-1']);
    $payment = PaymentData::from([
        'amount' => 500,
        'method' => PaymentMethod::CREDIT_CARD,
    ]);

    $invoice->item($item);
    $invoice->payment($payment);

    $invoice->getPayload();
})->throws(Exception::class, 'Payment method not configured.');
