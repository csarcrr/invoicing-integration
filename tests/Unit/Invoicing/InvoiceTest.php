<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\InvoicingIntegration;
use CsarCrr\InvoicingIntegration\Data\Invoice;
use CsarCrr\InvoicingIntegration\InvoicingItem;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('invoicing-integration.provider', 'vendus');
    config()->set('invoicing-integration.providers.vendus.key', '1234');
    config()->set('invoicing-integration.providers.vendus.mode', 'test');
    config()->set('invoicing-integration.providers.vendus.config.payments', [
        DocumentPaymentMethod::MB->value => 19999,
        DocumentPaymentMethod::CREDIT_CARD->value => 29999,
        DocumentPaymentMethod::CURRENT_ACCOUNT->value => 39999,
        DocumentPaymentMethod::MONEY->value => 49999,
        DocumentPaymentMethod::MONEY_TRANSFER->value => 59999,
    ]);
});

it('can invoice successfully with minimum data', function () {
    Http::fake([
        'vendus.pt/*' => Http::response(['number' => 'FT 10000'], 200),
    ]);

    $invoice = InvoicingIntegration::create();
    $invoice->addItem(new InvoicingItem('reference-1'));

    $response = $invoice->invoice();

    expect($response)->toBeInstanceOf(Invoice::class);
    expect($response->sequence())->toBe('FT 10000');
});
