<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\TaxData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Facades\Tax;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
});

test('maps the provider response onto a list of TaxData', function () {
    Http::fake(mockResponse(fixtures()->response()->tax()->files('response_multiple')));

    $list = Tax::find()->execute()->getList();

    expect($list)->toHaveCount(5)
        ->and($list->first())->toBeInstanceOf(TaxData::class)
        ->and($list->first()->id)->toBe(3951102)
        ->and($list->first()->name)->toBe('IVA Normal')
        ->and($list->first()->rate)->toBe(23.0)
        ->and($list->first()->fiscalZone)->toBe('PT');
});

test('maps the provider vat type onto the tax type', function () {
    Http::fake(mockResponse(fixtures()->response()->tax()->files('response_multiple')));

    expect(Tax::find()->execute()->getList()->pluck('type')->toArray())->toBe([
        ItemTax::NORMAL,
        ItemTax::REDUCED,
        ItemTax::NORMAL,
        ItemTax::EXEMPT,
        null,
    ]);
});

test('maps the provider exemption reason', function () {
    Http::fake(mockResponse(fixtures()->response()->tax()->files('response_multiple')));

    expect(Tax::find()->execute()->getList()->pluck('exemptionReason')->toArray())->toBe([
        null,
        null,
        null,
        TaxExemptionReason::M07,
        null,
    ]);
});

test('sends the request to the taxes endpoint', function () {
    Http::fake(mockResponse([]));

    Tax::find()->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'taxes/getAll'));
});
