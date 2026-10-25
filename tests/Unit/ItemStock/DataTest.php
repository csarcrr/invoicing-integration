<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ItemStockData;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Exceptions\Items\UnsupportedQuantityException;

it('accepts negative quantities', function (int|float $quantity) {
    $movement = StockMovementData::make(['itemId' => 1542, 'quantity' => $quantity]);

    expect($movement->quantity)->toBe($quantity);
})->with([-5, -0.5]);

it('fails with a zero quantity', function (int|float $quantity) {
    StockMovementData::make(['itemId' => 1542, 'quantity' => $quantity]);
})->with([0, 0.0])->throws(UnsupportedQuantityException::class);

it('defaults every field to null', function () {
    $movement = StockMovementData::make([]);

    expect($movement->id)->toBeNull()
        ->and($movement->itemId)->toBeNull()
        ->and($movement->quantity)->toBeNull()
        ->and($movement->date)->toBeNull()
        ->and($movement->warehouseId)->toBeNull()
        ->and($movement->notes)->toBeNull();
});

it('keeps the item stock quantity as int or float', function (int|float $quantity) {
    $stock = ItemStockData::make(['itemId' => 1542, 'warehouseId' => 1, 'quantity' => $quantity]);

    expect($stock->quantity)->toBe($quantity);
})->with([42, 42.5]);
