<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CompanyData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Company;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
});

test('maps the provider response onto a list of CompanyData', function () {
    Http::fake(mockResponse(fixtures()->response()->company()->files('response_multiple')));

    $list = Company::find()->execute()->getList();

    expect($list)->toHaveCount(2)
        ->and($list->first())->toBeInstanceOf(CompanyData::class)
        ->and($list->first()->id)->toBe(5)
        ->and($list->first()->name)->toBe('Empresa Demonstração')
        ->and($list->first()->vat)->toBe('508252252')
        ->and($list->last()->id)->toBe(405823);
});

test('sends the request to the companies endpoint', function () {
    Http::fake(mockResponse([]));

    Company::find()->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'companies/getAll'));
});

test('returns an empty list when the user has no companies', function () {
    Http::fake(mockResponse([]));

    expect(Company::find()->execute()->getList())->toBeEmpty();
});
