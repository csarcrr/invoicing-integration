<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod as PaymentMethodEnum;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;
use CsarCrr\InvoicingIntegration\Facades\PaymentMethod;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('maps the provider response onto a list of PaymentMethodData', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->paymentMethod()->files('response_multiple')));

    $list = PaymentMethod::find()->execute()->getList();

    expect($list)->toHaveCount(match ($provider) {
        Provider::CEGID_VENDUS => 6,
        Provider::MOLONI => 5,
    })
        ->and($list->first())->toBeInstanceOf(PaymentMethodData::class)
        ->and($list->first()->id)->toBe(7001)
        ->and($list->first()->name)->toBe('Numerário');
})->with('providers');

test('maps the provider payment method kind onto the payment method type', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->paymentMethod()->files('response_multiple')));

    $list = PaymentMethod::find()->execute()->getList();

    expect($list->pluck('type')->toArray())->toBe(match ($provider) {
        Provider::CEGID_VENDUS => [
            PaymentMethodEnum::MONEY,
            PaymentMethodEnum::MB,
            PaymentMethodEnum::CREDIT_CARD,
            PaymentMethodEnum::MONEY_TRANSFER,
            null,
            PaymentMethodEnum::CURRENT_ACCOUNT,
        ],
        Provider::MOLONI => [
            PaymentMethodEnum::MONEY,
            PaymentMethodEnum::MB,
            PaymentMethodEnum::CREDIT_CARD,
            null,
            null,
        ],
    });
})->with('providers');

test('sends the request to the payment methods endpoint', function (Provider $provider) {
    Http::fake(mockResponse([]));

    PaymentMethod::find()->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => $request->method() === 'GET' && Str::contains($request->url(), 'documents/paymentmethods'),
        Provider::MOLONI => Str::contains($request->url(), 'paymentMethods/getAll')
            && Str::contains($request->body(), 'qty=50')
            && Str::contains($request->body(), 'offset=0'),
    });
})->with('providers');

test('returns an empty list when Cegid Vendus reports no data', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404));

    expect(PaymentMethod::find()->execute()->getList())->toBeEmpty();
});

test('reports a single page on Cegid Vendus', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(fixtures()->response()->paymentMethod()->files('response_multiple')));

    $results = PaymentMethod::find()->execute();

    expect($results->getTotalPages())->toBe(1)
        ->and(fn () => $results->next())->toThrow(NoMorePagesException::class);
});

test('can go to the next page and then go back on Moloni', function () {
    mockConfiguration(Provider::MOLONI);

    $response = fixtures()->response()->paymentMethod()->files('response_multiple');

    Http::fakeSequence()
        ->push($response)
        ->push(collect($response)->take(1)->toArray())
        ->push($response);

    $results = PaymentMethod::find()->execute();
    $results->next()->execute();

    expect($results->getCurrentPage())->toBe(2)
        ->and($results->getList())->toHaveCount(1);

    $results->previous()->execute();

    expect($results->getCurrentPage())->toBe(1)
        ->and($results->getList())->toHaveCount(5)
        ->and($results->getTotalPages())->toBeNull();
});

test('fails when attempting to go below the first page', function (Provider $provider) {
    expect(fn () => PaymentMethod::find()->page(0))->toThrow(NoMorePagesException::class);
})->with('providers');
