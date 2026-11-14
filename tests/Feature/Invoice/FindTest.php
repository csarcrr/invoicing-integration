<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

test('finding invoices returns a list of InvoiceData', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('find')));

    $results = Invoice::find(InvoiceData::from(['type' => InvoiceType::Invoice]))->execute()->getList();

    expect($results)->toBeInstanceOf(Collection::class)
        ->and($results)->toHaveCount(2)
        ->and($results->first())->toBeInstanceOf(InvoiceData::class);
})->with('providers');

test('maps each stored document onto InvoiceData', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('find')));

    $first = Invoice::find()->execute()->getList()->first();

    expect($first->id)->toBe(1001)
        ->and($first->sequence)->toBe(match ($provider) {
            Provider::CEGID_VENDUS => 'FT 01P2026/12',
            Provider::MOLONI => 'FT A/12',
        })
        ->and($first->total)->toBe(3000)
        ->and($first->totalNet)->toBe(2439)
        ->and($first->type)->toBe(InvoiceType::Invoice);
})->with('providers');

test('keeps unlisted response fields as additional data on each invoice', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('find')));

    $results = Invoice::find()->execute()->getList();

    expect($results->first()->getAdditionalData())->toHaveKey('date')
        ->and($results->last()->getAdditionalData())->toHaveKey('date');
})->with('providers');

test('reports the total pages the provider returns', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->invoice()->files('find'), 200, ['X-Paginator-Pages' => 5]));

    $results = Invoice::find()->execute();

    expect($results->getTotalPages())->toBe(match ($provider) {
        Provider::CEGID_VENDUS => 5,
        Provider::MOLONI => null,
    });
})->with('providers');

test('returns an empty list when cegid vendus has no invoices', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404));

    expect(Invoice::find()->execute()->getList())->toBeEmpty();
})->note('todo: discover the reason for this to be a vendus specific test only');
