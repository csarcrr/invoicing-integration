<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;

it('transforms the stock movement update to provider payload', function (Provider $provider) {
    $update = fn () => ProductStock::update(StockMovementData::make([
        'id' => 777,
        'productId' => 1542,
        'quantity' => 10,
        'date' => Carbon::create(2026, 10, 22, 10),
        'warehouseId' => 3,
        'notes' => 'Stock recount',
    ]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($update)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($update()->getPayload()->sortKeys()->toArray())
            ->toBe(fixtures()->request()->productStock()->files('update')),
    };
})->with('providers');

it('only sends the fields that are set', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support product stocks.');
    }

    $payload = ProductStock::update(StockMovementData::make(['id' => 777, 'productId' => 1542]))
        ->getPayload()
        ->toArray();

    expect($payload)->toBe([
        'product_stock_id' => 777,
        'product_id' => 1542,
    ]);
})->with('providers');

it('sends a float quantity unchanged', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support product stocks.');
    }

    $payload = ProductStock::update(StockMovementData::make(['id' => 777, 'productId' => 1542, 'quantity' => -0.5]))
        ->getPayload()
        ->toArray();

    expect($payload['qty'])->toBe(-0.5);
})->with('providers');

it('fails when a required field is missing', function (Provider $provider, array $data, string $message) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support product stocks.');
    }

    expect(fn () => ProductStock::update(StockMovementData::make($data))->getPayload())
        ->toThrow(InvalidArgumentException::class, $message);
})->with('providers')->with([
    'missing movement id' => [['productId' => 1542], 'Movement ID is required.'],
    'missing product id' => [['id' => 777], 'Product ID is required.'],
]);
