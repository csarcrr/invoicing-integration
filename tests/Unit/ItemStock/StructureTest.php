<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldDeleteItemStock;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldMoveItemStock;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;

it('is an instance of the move contract', function (Provider $provider) {
    expect(ItemStock::move(StockMovementData::make([])))->toBeInstanceOf(ShouldMoveItemStock::class);
})->with('providers');

it('is an instance of the delete contract', function (Provider $provider) {
    $delete = fn () => ItemStock::delete(StockMovementData::make([]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($delete)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($delete())->toBeInstanceOf(ShouldDeleteItemStock::class),
    };
})->with('providers');
