<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use Illuminate\Support\Collection;

it('has a valid payment payload', function () {
    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new InvoicePayment(amount: 500, method: DocumentPaymentMethod::MB);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice)
        ->payments(collect([$payment]));

    $resolve->create();

    expect($resolve->payload()->get('payments'))
        ->toBeInstanceOf(Collection::class);
    expect($resolve->payload()->get('payments')->first()['amount'])->toBe(5.0);
    expect($resolve->payload()->get('payments')->first()['id'])->toBe(19999);
});

it('fails when no payment id is configured', function () {
    config()->set('invoicing-integration.providers.cegid_vendus.config.payments', [
        DocumentPaymentMethod::MB->value => null,
        DocumentPaymentMethod::CREDIT_CARD->value => null,
        DocumentPaymentMethod::CURRENT_ACCOUNT->value => null,
        DocumentPaymentMethod::MONEY->value => null,
        DocumentPaymentMethod::MONEY_TRANSFER->value => null,
    ]);

    $item = new InvoiceItem(reference: 'reference-1');
    $item->setPrice(500);

    $payment = new InvoicePayment(amount: 500, method: DocumentPaymentMethod::MB);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice)
        ->payments(collect([$payment]));

    $resolve->create();
})->throws(
    Exception::class,
    'The provider configuration is missing payment method details.'
);
