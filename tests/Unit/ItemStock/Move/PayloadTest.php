<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;

it('transforms the stock movement to provider payload', function (Provider $provider) {
    $payload = ItemStock::move(StockMovementData::make([
        'itemId' => 1542,
        'quantity' => -5,
        'date' => Carbon::create(2026, 10, 22, 10),
        'warehouseId' => 3,
        'notes' => 'Broken units',
    ]))->getPayload()->sortKeys()->toArray();

    expect($payload)->toBe(fixtures()->request()->itemStock()->files('move'));
})->with('providers');

it('omits optional fields', function (Provider $provider) {
    Carbon::setTestNow(Carbon::create(2026, 10, 22, 10));

    $payload = ItemStock::move(StockMovementData::make(['itemId' => 1542, 'quantity' => 5]))
        ->getPayload()
        ->toArray();

    expect($payload)->toBe(match ($provider) {
        Provider::CEGID_VENDUS => [
            'product_id' => 1542,
            'type' => 'add',
            'amount' => 5,
        ],
        Provider::MOLONI => [
            'product_id' => 1542,
            'qty' => 5,
            'movement_date' => '2026-10-22 10:00:00',
        ],
    });
})->with('providers');

it('sends a float quantity unchanged', function (Provider $provider) {
    $payload = ItemStock::move(StockMovementData::make(['itemId' => 1542, 'quantity' => -0.5]))
        ->getPayload()
        ->toArray();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload['amount'])->toBe(0.5)
            ->and($payload['type'])->toBe('remove'),
        Provider::MOLONI => expect($payload['qty'])->toBe(-0.5),
    };
})->with('providers');

it('builds the observations from the date and the notes', function (array $data, string $observations) {
    mockConfiguration(Provider::CEGID_VENDUS);

    $payload = ItemStock::move(StockMovementData::make(['itemId' => 1542, 'quantity' => 5, ...$data]))
        ->getPayload()
        ->toArray();

    expect($payload['obs'])->toBe($observations);
})->with([
    'date only' => [['date' => Carbon::create(2026, 10, 22, 10)], '2026-10-22 10:00:00'],
    'notes only' => [['notes' => 'Supplier delivery'], 'Supplier delivery'],
]);

it('fails when a required field is missing', function (Provider $provider, array $data, string $message) {
    expect(fn () => ItemStock::move(StockMovementData::make($data))->getPayload())
        ->toThrow(InvalidArgumentException::class, $message);
})->with('providers')->with([
    'missing item id' => [['quantity' => 5], 'Item ID is required.'],
    'missing quantity' => [['itemId' => 1542], 'Quantity is required.'],
]);
