<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionCanOnlyBeUsedWithExemptTaxException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionLawCanOnlyBeUsedWithExemptionException;
use CsarCrr\InvoicingIntegration\Exceptions\Items\UnsupportedQuantityException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

it('transforms to provider payload with all item properties', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->item()->files($fixtureName);

    $item = ItemData::make([
        'reference' => 'reference-1',
        'quantity' => 2,
        'price' => 1000,
        'note' => 'This is a test item',
        'percentageDiscount' => 10,
    ]);

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [$item],
    ]));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['item']);

it('sends the amount discount only to providers that support it', function (Provider $provider) {
    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1', 'amountDiscount' => 50])],
    ]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($invoice->getPayload()->get('items')->first()['discount_amount'])->toBe(0.5),
        Provider::MOLONI => expect($invoice->getPayload()->get('products')[0])->not->toHaveKey('discount'),
    };
})->with('providers');

it('transforms to provider payload with multiple items', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->item()->files($fixtureName);

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [
            ItemData::from(['reference' => 'reference-1']),
            ItemData::from(['reference' => 'reference-2']),
        ],
    ]));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['multiple_items']);

it('transforms to provider payload with correct item type', function (Provider $provider, string $fixtureName, ItemType $type) {
    $data = fixtures()->request()->invoice()->item()->type()->files($fixtureName);

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [
            ItemData::from(['reference' => 'reference-1', 'type' => $type]),
        ],
    ]));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers')->with([
    ['item_type_product', ItemType::Product],
    ['item_type_service', ItemType::Service],
    ['item_type_tax', ItemType::Tax],
    ['item_type_special_tax', ItemType::SpecialTax],
    ['item_type_other', ItemType::Other],
]);

it('transforms to provider payload with correct tax type', function (Provider $provider, string $fixtureName, ItemTax $taxType) {
    $data = fixtures()->request()->invoice()->item()->tax()->files($fixtureName);

    $attributes = [
        'reference' => 'reference-1',
        'tax' => $taxType,
    ];

    if ($taxType === ItemTax::EXEMPT) {
        $attributes['taxExemptionReason'] = TaxExemptionReason::M04;
        $attributes['taxExemptionLaw'] = TaxExemptionReason::M04->laws()[0];
    }

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [
            ItemData::from($attributes),
        ],
    ]));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers')->with([
    ['item_tax_normal', ItemTax::NORMAL],
    ['item_tax_reduced', ItemTax::REDUCED],
    ['item_tax_other', ItemTax::OTHER],
    ['item_tax_intermediate', ItemTax::INTERMEDIATE],
    ['item_tax_ise', ItemTax::EXEMPT],
]);

it('sends the item id to the provider when it identifies items by id', function (Provider $provider) {
    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['id' => 1542, 'reference' => 'reference-1'])],
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('items')->first()['id'])->toBe(1542),
        Provider::MOLONI => expect($payload->get('products')[0]['product_id'])->toBe(1542),
    };
})->with('providers');

it('sends the item id to the provider when the item has no reference', function (Provider $provider) {
    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['id' => 1542])],
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('items')->first())->toHaveKey('id', 1542)
            ->not->toHaveKey('reference'),
        Provider::MOLONI => expect($payload->get('products')[0]['product_id'])->toBe(1542),
    };
})->with('providers');

it('sends the item name as the line name', function (Provider $provider) {
    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1', 'name' => 'Bananas'])],
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('items')->first()['title'])->toBe('Bananas'),
        Provider::MOLONI => expect($payload->get('products')[0]['name'])->toBe('Bananas'),
    };
})->with('providers');

it('sends a quantity of one when the item has no quantity', function (Provider $provider) {
    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('items')->first()['qty'])->toBe(1),
        Provider::MOLONI => expect($payload->get('products')[0]['qty'])->toBe(1),
    };
})->with('providers');

it('sends the product type on vendus invoice lines when the item has no type', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
    ]))->getPayload();

    expect($payload->get('items')->first()['type_id'])->toBe('P');
});

it('holds null in the fields without a value and does not throw', function () {
    $item = ItemData::from([]);

    expect($item->quantity)->toBeNull()
        ->and($item->type)->toBeNull()
        ->and($item->controlStock)->toBeNull()
        ->and($item->enabled)->toBeNull();
});

it('fails when unsupported quantities are provided', function (mixed $invalidQuantity) {
    ItemData::from([
        'reference' => 'reference-1',
        'quantity' => $invalidQuantity,
    ]);
})->with([-1, 0])->throws(UnsupportedQuantityException::class);

it('fails when attempting to use tax exemption with non-exempt tax', function () {
    ItemData::from([
        'reference' => 'reference-1',
        'tax' => ItemTax::NORMAL,
        'taxExemptionReason' => TaxExemptionReason::M04,
    ]);
})->throws(ExemptionCanOnlyBeUsedWithExemptTaxException::class);

it('fails when attempting to set tax exemption law without exemption reason', function () {
    ItemData::from([
        'reference' => 'reference-1',
        'tax' => ItemTax::EXEMPT,
        'taxExemptionLaw' => TaxExemptionReason::M04->laws()[0],
    ]);
})->throws(ExemptionLawCanOnlyBeUsedWithExemptionException::class);
