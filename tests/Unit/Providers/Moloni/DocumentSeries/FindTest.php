<?php

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Moloni\DocumentSeries\Find;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    mockConfiguration(Provider::MOLONI);
});

it('posts to the document sets get all endpoint', function () {
    $find = new Find;

    expect($find->getUrl())->toBe('documentSets/getAll')
        ->and($find->getPayload()->toArray())->toBe(['qty' => 50, 'offset' => 0]);
});
