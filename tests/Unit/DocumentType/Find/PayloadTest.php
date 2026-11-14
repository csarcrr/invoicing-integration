<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\DocumentType;

it('builds the find url', function (Provider $provider) {
    $find = DocumentType::find();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find->getUrl())->toBe('documents/types'),
        Provider::MOLONI => expect($find->getUrl())->toBe('documents/getAllDocumentTypes'),
    };
})->with('providers');

it('builds an empty payload', function () {
    expect(DocumentType::find()->getPayload()->toArray())->toBe([]);
})->with('providers');
