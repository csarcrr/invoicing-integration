<?php

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Moloni\DocumentSeries\Find;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    config()->set('cache.default', 'array');
    Cache::flush();
    mockConfiguration(Provider::MOLONI);
});

it('posts to the document sets get all endpoint', function () {
    (new Find)->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'documentSets/getAll')
        && Str::contains($request->body(), 'company_id=123456')
        && Str::contains($request->body(), 'qty=50')
        && Str::contains($request->body(), 'offset=0'));
});

it('returns the default document set id', function () {
    expect((new Find)->execute()->getDefaultId())->toBe(8002);
});

it('returns the first document set id when none is the default', function () {
    Http::swap(new Factory);
    Http::fake([
        '*documentSets/getAll*' => mockResponse([
            ['document_set_id' => 8001, 'name' => 'A', 'active_by_default' => 0],
            ['document_set_id' => 8002, 'name' => 'B', 'active_by_default' => 0],
        ]),
    ]);

    expect((new Find)->execute()->getDefaultId())->toBe(8001);
});
