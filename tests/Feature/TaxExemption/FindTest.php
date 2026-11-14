<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\TaxExemptionData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\TaxExemption;
use Illuminate\Support\Facades\Http;

test('maps the provider response onto a list of TaxExemptionData', function () {
    Http::fake(mockResponse(fixtures()->response()->taxExemption()->files('response_multiple')));

    $list = TaxExemption::find()->execute()->getList();

    expect($list)->toHaveCount(2)
        ->and($list->first())->toBeInstanceOf(TaxExemptionData::class)
        ->and($list->pluck('code')->toArray())->toBe(['M07', 'M40'])
        ->and($list->pluck('description')->toArray())->toBe([
            'Isento Artigo 9.º do CIVA',
            'Artigo 6.º n.º 6 alínea a) do CIVA, a contrário',
        ]);
})->with('providers');

test('keeps the extra response fields as additional data', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->taxExemption()->files('response_multiple')));

    $taxExemption = TaxExemption::find()->execute()->getList()->first();

    expect($taxExemption->getAdditionalData())->toMatchArray(match ($provider) {
        Provider::CEGID_VENDUS => ['law' => ['Artigo 9.º do CIVA']],
        Provider::MOLONI => ['visibility' => 1],
    });
})->with('providers');

test('returns an empty list when the provider has no tax exemptions', function () {
    Http::fake(mockResponse([]));

    expect(TaxExemption::find()->execute()->getList())->toBeEmpty();
})->with('providers');

test('returns an empty list when Cegid Vendus reports no data', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404));

    expect(TaxExemption::find()->execute()->getList())->toBeEmpty();
});
