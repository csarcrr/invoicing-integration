<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\Action;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Providers\CegidVendus;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;

it('can assign a payment', function (CreateInvoice $invoice, Fixtures $fixture) {
    $item = new Item(reference: 'reference-1');

    $payment = new Payment(amount: 500, method: PaymentMethod::CREDIT_CARD);

    $invoice->item($item);
    $invoice->payment($payment);

    expect($invoice->getPayments())->toHaveCount(1);
})->with('create-invoice');

it('can assign multiple payments', function (CreateInvoice $invoice, Fixtures $fixture) {
    $item = new Item(reference: 'reference-1');

    $payment1 = new Payment(amount: 300, method: PaymentMethod::CREDIT_CARD);
    $payment2 = new Payment(amount: 200, method: PaymentMethod::MONEY);

    $invoice->item($item);
    $invoice->payment($payment1);
    $invoice->payment($payment2);

    expect($invoice->getPayments())->toHaveCount(2);
})->with('create-invoice');

it('has expected payload', function (CreateInvoice $invoice, Fixtures $fixture, string $fixtureName) {
    $data = $fixture->request()->invoice()->payment()->files($fixtureName);

    $item = new Item(reference: 'reference-1');
    $payment = new Payment(amount: 500, method: PaymentMethod::CREDIT_CARD);

    $invoice->item($item);
    $invoice->payment($payment);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['payment']);

it('has expected payload with multiple payments', function (CreateInvoice $invoice, Fixtures $fixture, string $fixtureName) {
    $data = $fixture->request()->invoice()->payment()->files($fixtureName);

    $item = new Item(reference: 'reference-1');
    $payment = new Payment(amount: 500, method: PaymentMethod::CREDIT_CARD);
    $payment2 = new Payment(amount: 500, method: PaymentMethod::MONEY);

    $invoice->item($item);
    $invoice->payment($payment);
    $invoice->payment($payment2);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['payment_multiple']);

it('throws error when configuration is not set', function (CreateInvoice $invoice) {
    config()->set('invoicing-integration.providers.'.IntegrationProvider::CEGID_VENDUS->value.'.config', [
        'payments' => [
            PaymentMethod::CREDIT_CARD->value => null,
            PaymentMethod::MONEY->value => null,
            PaymentMethod::MB->value => null,
            PaymentMethod::MONEY_TRANSFER->value => null,
            PaymentMethod::CURRENT_ACCOUNT->value => null,
        ],
    ]);

    $item = new Item(reference: 'reference-1');
    $payment = new Payment(amount: 500, method: PaymentMethod::CREDIT_CARD);

    $invoice->item($item);
    $invoice->payment($payment);

    $invoice->getPayload();
})->with([
    [fn () => CegidVendus::invoice(Action::CREATE)],
])->throws(Exception::class, 'Payment method not configured.');
