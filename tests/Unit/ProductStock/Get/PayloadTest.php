<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;

it('sends the item id in the request', function (Provider $provider) {
    $get = fn () => ProductStock::get(ItemData::make(['id' => 1542]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($get)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($get()->getPayload()->toArray())->toBe(['product_id' => 1542]),
    };
})->with('providers');

it('fails when no product id is set', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support product stocks.');
    }

    expect(fn () => ProductStock::get(ItemData::make([]))->getPayload())
        ->toThrow(InvalidArgumentException::class, 'Product ID is required.');
})->with('providers');
