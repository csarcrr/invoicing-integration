<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;

it('transforms the stock movement deletion to provider payload', function (Provider $provider) {
    $delete = fn () => ProductStock::delete(StockMovementData::make(['id' => 777]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($delete)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($delete()->getPayload()->toArray())
            ->toBe(fixtures()->request()->productStock()->files('delete')),
    };
})->with('providers');

it('fails when no movement id is set', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support product stocks.');
    }

    expect(fn () => ProductStock::delete(StockMovementData::make([]))->getPayload())
        ->toThrow(InvalidArgumentException::class, 'Movement ID is required.');
})->with('providers');
