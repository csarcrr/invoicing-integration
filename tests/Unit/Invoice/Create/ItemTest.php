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
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

it('can assign an item with all properties', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->item()->files($fixtureName);

    $invoice = Invoice::create();
    $item = new Item;
    $item->reference('reference-1')
        ->quantity(2)
        ->price(1000)
        ->note('This is a test item')
        ->percentageDiscount(10)
        ->amountDiscount(50);

    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['item']);

it('can assign multiple items', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->item()->files($fixtureName);

    $invoice = Invoice::create();
    $invoice->item(new Item('reference-1'));
    $invoice->item(new Item('reference-2'));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['multiple_items']);

it('sets item types correctly', function (Provider $provider, string $fixtureName, ItemType $type) {
    $data = fixtures()->request()->invoice()->item()->type()->files($fixtureName);

    $invoice = Invoice::create();
    $item = new Item(reference: 'reference-1');

    $item->type($type);

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
    $item = new Item(reference: 'reference-1');

    $item->tax($taxType);

    if ($taxType === ItemTax::EXEMPT) {
        $item->taxExemption(TaxExemptionReason::M04);
        $item->taxExemptionLaw(TaxExemptionReason::M04->laws()[0]);
    }

    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers')->with([
    ['item_tax_normal', ItemTax::NORMAL],
    ['item_tax_reduced', ItemTax::REDUCED],
    ['item_tax_other', ItemTax::OTHER],
    ['item_tax_intermediate', ItemTax::INTERMEDIATE],
    ['item_tax_ise', ItemTax::EXEMPT],
]);

it('has the item always with the default quantity of one', function () {
    $item = new Item('reference-1');

    expect($item->getQuantity())->toBe(1);
});

it('fails when unsupported quantities are provided', function (mixed $invalidQuantity) {
    $item = new Item('reference-1');
    $item->quantity($invalidQuantity);

    expect($item->getQuantity())->toBe(1);
})->with([-1, 0])->throws(UnsupportedQuantityException::class);

it('fails when attempting to use tax exemption with non-exempt tax', function () {
    $item = new Item('reference-1');
    $item->tax(ItemTax::NORMAL);
    $item->taxExemption(TaxExemptionReason::M04);
})->throws(ExemptionCanOnlyBeUsedWithExemptTaxException::class);

it('fails when attempting to set tax exemption law without exemption reason', function () {
    $item = new Item('reference-1');
    $item->tax(ItemTax::EXEMPT);
    $item->taxExemptionLaw(TaxExemptionReason::M04->laws()[0]);
})->throws(ExemptionLawCanOnlyBeUsedWithExemptionException::class);
