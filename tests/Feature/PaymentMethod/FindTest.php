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

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
});

test('maps the provider response onto a list of PaymentMethodData', function () {
    Http::fake(mockResponse(fixtures()->response()->paymentMethod()->files('response_multiple')));

    $list = PaymentMethod::find()->execute()->getList();

    expect($list)->toHaveCount(5)
        ->and($list->first())->toBeInstanceOf(PaymentMethodData::class)
        ->and($list->first()->id)->toBe(7001)
        ->and($list->first()->name)->toBe('Numerário');
});

test('maps the provider flags onto the payment method type', function () {
    Http::fake(mockResponse(fixtures()->response()->paymentMethod()->files('response_multiple')));

    $list = PaymentMethod::find()->execute()->getList();

    expect($list->pluck('type')->toArray())->toBe([
        PaymentMethodEnum::MONEY,
        PaymentMethodEnum::MB,
        PaymentMethodEnum::CREDIT_CARD,
        null,
        null,
    ]);
});

test('automagically injects provider pagination details into the request', function () {
    Http::fake(mockResponse([]));

    PaymentMethod::find()->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'paymentMethods/getAll')
        && Str::contains($request->body(), 'qty=50')
        && Str::contains($request->body(), 'offset=0'));
});

test('can go to the next page and then go back', function () {
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

test('fails when attempting to go below the first page', function () {
    expect(fn () => PaymentMethod::find()->page(0))->toThrow(NoMorePagesException::class);
});
