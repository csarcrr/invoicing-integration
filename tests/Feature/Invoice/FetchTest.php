<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Support\Facades\Http;

test('an invoice get request maps the provider response onto InvoiceData', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('fetch')));

    $invoice = Invoice::fetch(InvoiceData::from(['id' => 123456]))->execute()->get();

    expect($invoice->id)->toBe(123456)
        ->and($invoice->sequence)->toBe(match ($provider) {
            Provider::VENDUS => 'FR 01P2026/12',
            Provider::MOLONI => 'FR A/12',
        })
        ->and($invoice->total)->toBe(3000)
        ->and($invoice->totalNet)->toBe(2439)
        ->and($invoice->type)->toBe(InvoiceType::InvoiceReceipt);

    Http::assertSentCount(1);
})->with('providers');

test('maps the provider atcud', function () {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('fetch')));

    $invoice = Invoice::fetch(InvoiceData::from(['id' => 123456]))->execute()->get();

    expect($invoice->atcud)->toBe('JFD5B7DW-12');
})->with('providers');

test('an empty provider atcud is null', function () {
    Http::fake(mockResponse([...fixtures()->response()->invoice()->files('fetch'), 'atcud' => '']));

    $invoice = Invoice::fetch(InvoiceData::from(['id' => 123456]))->execute()->get();

    expect($invoice->atcud)->toBeNull();
})->with('providers');

test('supported properties are not filled in additional data', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('fetch')));

    $invoice = Invoice::fetch(InvoiceData::from(['id' => 123456]))->execute()->get();

    match ($provider) {
        Provider::VENDUS => expect($invoice->getAdditionalData())
            ->not->toHaveKey('number')
            ->not->toHaveKey('amount_gross')
            ->not->toHaveKey('amount_net')
            ->not->toHaveKey('atcud'),
        Provider::MOLONI => expect($invoice->getAdditionalData())
            ->not->toHaveKey('number')
            ->not->toHaveKey('document_id')
            ->not->toHaveKey('net_value')
            ->not->toHaveKey('taxes_value')
            ->not->toHaveKey('products')
            ->not->toHaveKey('atcud'),
    };
})->with('providers');

test('unsupported properties are kept in additional data', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('fetch')));

    $invoice = Invoice::fetch(InvoiceData::from(['id' => 123456]))->execute()->get();

    expect($invoice->getAdditionalData())->toHaveKey('date');
})->with('providers');

test('an unknown document type is null', function (Provider $provider) {
    $fixture = fixtures()->response()->invoice()->files('fetch');

    Http::fake(mockResponse(match ($provider) {
        Provider::VENDUS => [...$fixture, 'type' => 'XX'],
        Provider::MOLONI => [...$fixture, 'document_type' => ['saft_code' => 'XX']],
    }));

    $invoice = Invoice::fetch(InvoiceData::from(['id' => 123456]))->execute()->get();

    expect($invoice->type)->toBeNull();
})->with('providers');

it('fails when the provider has no invoice with the id', function (Provider $provider) {
    Http::fake(match ($provider) {
        Provider::VENDUS => mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404),
        Provider::MOLONI => mockResponse([]),
    });

    Invoice::fetch(InvoiceData::from(['id' => 123456]))->execute();
})->with('providers')->throws(RequestFailedException::class);
