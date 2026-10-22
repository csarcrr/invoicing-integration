<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;

it('transforms the stock movement to provider payload', function (Provider $provider) {
    $move = fn () => ItemStock::move(StockMovementData::make([
        'itemId' => 1542,
        'quantity' => -5,
        'date' => Carbon::create(2026, 10, 22, 10),
        'warehouseId' => 3,
        'notes' => 'Broken units',
    ]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($move)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($move()->getPayload()->sortKeys()->toArray())
            ->toBe(fixtures()->request()->itemStock()->files('move')),
    };
})->with('providers');

it('defaults the movement date to now and omits optional fields', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support item stocks.');
    }

    Carbon::setTestNow(Carbon::create(2026, 10, 22, 10));

    $payload = ItemStock::move(StockMovementData::make(['itemId' => 1542, 'quantity' => 5]))
        ->getPayload()
        ->toArray();

    expect($payload)->toBe([
        'product_id' => 1542,
        'qty' => 5,
        'movement_date' => '2026-10-22 10:00:00',
    ]);
})->with('providers');

it('sends a float quantity unchanged', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support item stocks.');
    }

    $payload = ItemStock::move(StockMovementData::make(['itemId' => 1542, 'quantity' => -0.5]))
        ->getPayload()
        ->toArray();

    expect($payload['qty'])->toBe(-0.5);
})->with('providers');

it('fails when a required field is missing', function (Provider $provider, array $data, string $message) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support item stocks.');
    }

    expect(fn () => ItemStock::move(StockMovementData::make($data))->getPayload())
        ->toThrow(InvalidArgumentException::class, $message);
})->with('providers')->with([
    'missing item id' => [['quantity' => 5], 'Item ID is required.'],
    'missing quantity' => [['itemId' => 1542], 'Quantity is required.'],
]);
