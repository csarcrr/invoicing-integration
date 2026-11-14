<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Support\Facades\Http;

test('an invoice get request maps the provider response onto InvoiceData', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('get')));

    $invoice = Invoice::get(InvoiceData::from(['id' => 123456]))->execute()->getInvoice();

    expect($invoice->id)->toBe(123456)
        ->and($invoice->sequence)->toBe(match ($provider) {
            Provider::CEGID_VENDUS => 'FR 01P2026/12',
            Provider::MOLONI => 'FR A/12',
        })
        ->and($invoice->total)->toBe(3000)
        ->and($invoice->totalNet)->toBe(2439)
        ->and($invoice->type)->toBe(InvoiceType::InvoiceReceipt);

    Http::assertSentCount(1);
})->with('providers');

test('cegid vendus maps the atcud to atcudHash and moloni leaves it null', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('get')));

    $invoice = Invoice::get(InvoiceData::from(['id' => 123456]))->execute()->getInvoice();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($invoice->atcudHash)->toBe('JFD5B7DW-12'),
        Provider::MOLONI => expect($invoice->atcudHash)->toBeNull(),
    };
})->with('providers');

test('supported properties are not filled in additional data', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('get')));

    $invoice = Invoice::get(InvoiceData::from(['id' => 123456]))->execute()->getInvoice();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($invoice->getAdditionalData())
            ->not->toHaveKey('number')
            ->not->toHaveKey('amount_gross')
            ->not->toHaveKey('amount_net')
            ->not->toHaveKey('atcud'),
        Provider::MOLONI => expect($invoice->getAdditionalData())
            ->not->toHaveKey('number')
            ->not->toHaveKey('document_id')
            ->not->toHaveKey('net_value')
            ->not->toHaveKey('taxes_value')
            ->not->toHaveKey('products'),
    };
})->with('providers');

test('unsupported properties are kept in additional data', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('get')));

    $invoice = Invoice::get(InvoiceData::from(['id' => 123456]))->execute()->getInvoice();

    expect($invoice->getAdditionalData())->toHaveKey('date');
})->with('providers');

test('an unknown document type falls back to FT', function (Provider $provider) {
    $fixture = fixtures()->response()->invoice()->files('get');

    Http::fake(mockResponse(match ($provider) {
        Provider::CEGID_VENDUS => [...$fixture, 'type' => 'XX'],
        Provider::MOLONI => [...$fixture, 'document_type' => ['saft_code' => 'XX']],
    }));

    $invoice = Invoice::get(InvoiceData::from(['id' => 123456]))->execute()->getInvoice();

    expect($invoice->type)->toBe(InvoiceType::Invoice);
})->with('providers');
