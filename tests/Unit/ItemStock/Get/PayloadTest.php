<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;

it('sends the item id in the request', function (Provider $provider) {
    $get = fn () => ItemStock::get(ItemData::make(['id' => 1542]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($get)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($get()->getPayload()->toArray())->toBe(['product_id' => 1542]),
    };
})->with('providers');

it('fails when no item id is set', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support item stocks.');
    }

    expect(fn () => ItemStock::get(ItemData::make([]))->getPayload())
        ->toThrow(InvalidArgumentException::class, 'Item ID is required.');
})->with('providers');
