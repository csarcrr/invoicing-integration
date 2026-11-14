<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\TaxExemption;

it('builds the find url', function (Provider $provider) {
    $find = TaxExemption::find();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find->getUrl())->toBe('taxes/exemptions'),
        Provider::MOLONI => expect($find->getUrl())->toBe('taxExemptions/getAll'),
    };
})->with('providers');

it('builds an empty payload', function () {
    expect(TaxExemption::find()->getPayload()->toArray())->toBe([]);
})->with('providers');
