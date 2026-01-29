<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionCanOnlyBeUsedWithExemptTaxException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionLawCanOnlyBeUsedWithExemptionException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\UnsupportedQuantityException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\ItemData;

it('can assign an item with all properties', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->item()->files($fixtureName);

    $invoice = Invoice::create();
    $item = ItemData::from([
        'reference' => 'reference-1',
        'quantity' => 2,
        'price' => 1000,
        'note' => 'This is a test item',
        'percentageDiscount' => 10,
        'amountDiscount' => 50,
    ]);

    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['item']);

it('can assign multiple items', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->item()->files($fixtureName);

    $invoice = Invoice::create();
    $invoice->item(ItemData::from(['reference' => 'reference-1']));
    $invoice->item(ItemData::from(['reference' => 'reference-2']));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['multiple_items']);

it('sets item types correctly', function (Provider $provider, string $fixtureName, ItemType $type) {
    $data = fixtures()->request()->invoice()->item()->type()->files($fixtureName);

    $invoice = Invoice::create();
    $item = ItemData::from([
        'reference' => 'reference-1',
        'type' => $type,
    ]);

    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers')->with([
    ['item_type_product', ItemType::Product],
    ['item_type_service', ItemType::Service],
    ['item_type_tax', ItemType::Tax],
    ['item_type_special_tax', ItemType::SpecialTax],
    ['item_type_other', ItemType::Other],
]);

it('sets tax types correctly', function (Provider $provider, string $fixtureName, ItemTax $taxType) {
    $data = fixtures()->request()->invoice()->item()->tax()->files($fixtureName);

    $invoice = Invoice::create();
    $attributes = [
        'reference' => 'reference-1',
        'tax' => $taxType,
    ];

    if ($taxType === ItemTax::EXEMPT) {
        $attributes['taxExemptionReason'] = TaxExemptionReason::M04;
        $attributes['taxExemptionLaw'] = TaxExemptionReason::M04->laws()[0];
    }

    $invoice->item(ItemData::from($attributes));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers')->with([
    ['item_tax_normal', ItemTax::NORMAL],
    ['item_tax_reduced', ItemTax::REDUCED],
    ['item_tax_other', ItemTax::OTHER],
    ['item_tax_intermediate', ItemTax::INTERMEDIATE],
    ['item_tax_ise', ItemTax::EXEMPT],
]);

it('has the item always with the default quantity of one', function () {
    $item = ItemData::from(['reference' => 'reference-1']);

    expect($item->quantity)->toBe(1);
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
