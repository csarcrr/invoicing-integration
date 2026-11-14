<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\DocumentTypeData;
use CsarCrr\InvoicingIntegration\Data\LanguageData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\DocumentType;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

function fakeDocumentTypeResponse(Provider $provider, mixed $response, int $status = 200): void
{
    if ($provider === Provider::CEGID_VENDUS) {
        Http::fake(mockResponse($response, $status));

        return;
    }

    Http::fake([
        '*languages/getAll*' => mockResponse(fixtures()->response()->language()->files('response_multiple')),
        '*' => mockResponse($response, $status),
    ]);
}

test('maps the provider response onto a list of DocumentTypeData', function (Provider $provider) {
    fakeDocumentTypeResponse($provider, fixtures()->response()->documentType()->files('response_multiple'));

    $list = DocumentType::find()->execute()->getList();

    expect($list)->toHaveCount(2)
        ->and($list->first())->toBeInstanceOf(DocumentTypeData::class)
        ->and($list->pluck('code')->toArray())->toBe(['FT', 'NC'])
        ->and($list->pluck('name')->toArray())->toBe(['Fatura', 'Nota de Crédito']);
})->with('providers');

test('keeps the extra response fields as additional data', function (Provider $provider) {
    fakeDocumentTypeResponse($provider, fixtures()->response()->documentType()->files('response_multiple'));

    $documentType = DocumentType::find()->execute()->getList()->first();

    expect($documentType->getAdditionalData())->toMatchArray(match ($provider) {
        Provider::CEGID_VENDUS => ['visible_filter' => 'yes'],
        Provider::MOLONI => ['document_type_id' => 1],
    });
})->with('providers');

test('returns an empty list when the provider has no document types', function (Provider $provider) {
    fakeDocumentTypeResponse($provider, []);

    expect(DocumentType::find()->execute()->getList())->toBeEmpty();
})->with('providers');

test('returns an empty list when Cegid Vendus reports no data', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(['errors' => [['code' => 'A001', 'message' => 'No data']]], 404));

    expect(DocumentType::find()->execute()->getList())->toBeEmpty();
});

test('sends the Portuguese Moloni language id when no language is given', function () {
    mockConfiguration(Provider::MOLONI);

    fakeDocumentTypeResponse(Provider::MOLONI, fixtures()->response()->documentType()->files('response_multiple'));

    DocumentType::find()->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'documents/getAllDocumentTypes')
        && Str::contains($request->body(), 'language_id=1'));
});

test('sends the Moloni language id for the given language', function () {
    mockConfiguration(Provider::MOLONI);

    fakeDocumentTypeResponse(Provider::MOLONI, fixtures()->response()->documentType()->files('response_multiple'));

    DocumentType::find(LanguageData::from(['code' => 'en']))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'documents/getAllDocumentTypes')
        && Str::contains($request->body(), 'language_id=2'));
});

test('does not send a language to Cegid Vendus', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake(mockResponse(fixtures()->response()->documentType()->files('response_multiple')));

    DocumentType::find(LanguageData::from(['code' => 'en']))->execute();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'documents/types')
        && ! Str::contains($request->url(), 'language'));
});
