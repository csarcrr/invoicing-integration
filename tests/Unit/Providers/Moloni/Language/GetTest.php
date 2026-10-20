<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\LanguageData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetLanguageIdException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Language\Get;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    config()->set('cache.default', 'array');
    Cache::flush();
    mockConfiguration(Provider::MOLONI);
});

it('posts to the languages get all endpoint', function () {
    (new Get(LanguageData::from(['code' => 'PT'])))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'languages/getAll')
        && Str::contains($request->body(), 'company_id=123456'));
});

it('fills the language id and name matching the code', function (string $code, int $expectedId, string $expectedName) {
    $language = (new Get(LanguageData::from(['code' => $code])))->execute()->getLanguage();

    expect($language->id)->toBe($expectedId)
        ->and($language->name)->toBe($expectedName)
        ->and($language->code)->toBe($code);
})->with([
    ['PT', 1, 'Português'],
    ['pt', 1, 'Português'],
    ['EN', 2, 'English'],
]);

it('fails when the code is not a moloni language', function () {
    (new Get(LanguageData::from(['code' => 'XX'])))->execute();
})->throws(CouldNotGetLanguageIdException::class);
