<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Warehouse;

it('builds the find url', function (Provider $provider) {
    $find = Warehouse::find();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find->getUrl())->toBe('stores'),
        Provider::MOLONI => expect($find->getUrl())->toBe('warehouses/getAll'),
    };
})->with('providers');

it('builds an empty payload', function () {
    expect(Warehouse::find()->getPayload()->toArray())->toBe([]);
})->with('providers');
