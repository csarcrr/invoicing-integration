<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;
use CsarCrr\InvoicingIntegration\Facades\DueDate;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('maps the provider response onto a list of DueDateData', function (Provider $provider) {
    Http::fake(mockResponse(fixtures()->response()->dueDate()->files('response_multiple')));

    $list = DueDate::find()->execute()->get();

    expect($list)->toHaveCount(2)
        ->and($list->first())->toBeInstanceOf(DueDateData::class)
        ->and($list->pluck('id')->toArray())->toBe(match ($provider) {
            Provider::CEGID_VENDUS => [null, null],
            Provider::MOLONI => [4321, 4322],
        })
        ->and($list->pluck('name')->toArray())->toBe(['Pronto Pagamento', '30 dias'])
        ->and($list->pluck('days')->toArray())->toBe([0, 30]);
})->with('providers');

test('keeps the Cegid Vendus key as additional data', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(fixtures()->response()->dueDate()->files('response_multiple')));

    $list = DueDate::find()->execute()->get();

    expect($list->first()->getAdditionalData())->toMatchArray(['key' => 'now'])
        ->and($list->last()->getAdditionalData())->toMatchArray(['key' => '30']);
});

test('sends the request to the due dates endpoint', function (Provider $provider) {
    Http::fake(mockResponse([]));

    DueDate::find()->execute();

    Http::assertSent(fn (Request $request) => match ($provider) {
        Provider::CEGID_VENDUS => $request->method() === 'GET' && Str::contains($request->url(), 'documents/duedates'),
        Provider::MOLONI => Str::contains($request->url(), 'maturityDates/getAll')
            && Str::contains($request->body(), 'qty=50')
            && Str::contains($request->body(), 'offset=0'),
    });
})->with('providers');

test('can go to the next page and then go back', function () {
    mockConfiguration(Provider::MOLONI);

    $response = fixtures()->response()->dueDate()->files('response_multiple');

    Http::fakeSequence()
        ->push($response)
        ->push(collect($response)->take(1)->toArray())
        ->push($response);

    $results = DueDate::find()->execute();
    $results->next()->execute();

    expect($results->getCurrentPage())->toBe(2)
        ->and($results->get())->toHaveCount(1);

    $results->previous()->execute();

    expect($results->getCurrentPage())->toBe(1)
        ->and($results->get())->toHaveCount(2)
        ->and($results->getTotalPages())->toBeNull();
});

test('fails when attempting to go below the first page', function () {
    expect(fn () => DueDate::find()->page(0))->toThrow(NoMorePagesException::class);
})->with('providers');

test('returns an empty list when Cegid Vendus reports no data', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404));

    expect(DueDate::find()->execute()->get())->toBeEmpty();
});
