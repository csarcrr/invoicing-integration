<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\LanguageData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Language\Get;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    mockConfiguration(Provider::MOLONI);
});

it('posts to the languages get all endpoint', function () {
    expect((new Get(LanguageData::from(['code' => 'PT'])))->getUrl())->toBe('languages/getAll');
});
