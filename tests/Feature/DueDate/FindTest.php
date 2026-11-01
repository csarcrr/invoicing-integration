<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Pagination\NoMorePagesException;
use CsarCrr\InvoicingIntegration\Facades\DueDate;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    mockConfiguration(Provider::MOLONI);
});

test('maps the provider response onto a list of DueDateData', function () {
    Http::fake(mockResponse(fixtures()->response()->dueDate()->files('response_multiple')));

    $list = DueDate::find()->execute()->getList();

    expect($list)->toHaveCount(2)
        ->and($list->first())->toBeInstanceOf(DueDateData::class)
        ->and($list->first()->id)->toBe(4321)
        ->and($list->first()->name)->toBe('Pronto Pagamento')
        ->and($list->first()->days)->toBe(0)
        ->and($list->last()->days)->toBe(30);
});

test('automagically injects provider pagination details into the request', function () {
    Http::fake(mockResponse([]));

    DueDate::find()->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'maturityDates/getAll')
        && Str::contains($request->body(), 'qty=50')
        && Str::contains($request->body(), 'offset=0'));
});

test('can go to the next page and then go back', function () {
    $response = fixtures()->response()->dueDate()->files('response_multiple');

    Http::fakeSequence()
        ->push($response)
        ->push(collect($response)->take(1)->toArray())
        ->push($response);

    $results = DueDate::find()->execute();
    $results->next()->execute();

    expect($results->getCurrentPage())->toBe(2)
        ->and($results->getList())->toHaveCount(1);

    $results->previous()->execute();

    expect($results->getCurrentPage())->toBe(1)
        ->and($results->getList())->toHaveCount(2)
        ->and($results->getTotalPages())->toBeNull();
});

test('fails when attempting to go below the first page', function () {
    expect(fn () => DueDate::find()->page(0))->toThrow(NoMorePagesException::class);
});
