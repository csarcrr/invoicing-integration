<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Facades\InvoicingIntegration;
use CsarCrr\InvoicingIntegration\Data\Invoice;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\InvoicingItem;
use CsarCrr\InvoicingIntegration\InvoicingPayment;
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

it('can invoice and emit a receipt for that invoice', function () {
    Http::fake([
        'vendus.pt/*' => Http::response(['number' => 'FT 10000'], 200),
        'vendus.pt/*' => Http::response(['number' => 'RG 10000'], 200),
    ]);

    $item = new InvoicingItem('reference-1');
    $item->setPrice(500);

    $invoice = InvoicingIntegration::create();
    $invoice->setType(DocumentType::Invoice);
    $invoice->addItem($item);

    $details = $invoice->invoice();

    $receipt = InvoicingIntegration::create();
    $receipt->setType(DocumentType::Receipt);
    $receipt->addPayment(new InvoicingPayment(DocumentPaymentMethod::MONEY, 500));
    $receipt->addRelatedDocument($details->sequence());

    $details = $receipt->invoice();

    expect($details)->toBeInstanceOf(Invoice::class);
    expect($details->sequence())->toBe('RG 10000');
});
