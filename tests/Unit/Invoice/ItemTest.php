<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\UnsupportedQuantityException;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

it('can assign an item with all properties', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName
) {
    $data = $fixture->request()->invoice()->item()->files($fixtureName);

    $item = new Item;
    $item->reference('reference-1')
        ->quantity(2)
        ->price(1000)
        ->note('This is a test item')
        ->percentageDiscount(10)
        ->amountDiscount(50);

    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('invoice-full', ['item']);

it('can assign multiple items', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName
) {
    $data = $fixture->request()->invoice()->item()->files($fixtureName);

    $invoice->item(new Item('reference-1'));
    $invoice->item(new Item('reference-2'));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('invoice-full', ['multiple_items']);

it('sets item types correctly', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName,
    ItemType $type
) {
    $data = $fixture->request()->invoice()->item()->type()->files($fixtureName);

    $item = new Item(reference: 'reference-1');

    $item->type($type);

    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('invoice-full', [
    ['item_type_product', ItemType::Product],
    ['item_type_service', ItemType::Service],
    ['item_type_tax', ItemType::Tax],
    ['item_type_special_tax', ItemType::SpecialTax],
    ['item_type_other', ItemType::Other],
]);

it('sets tax types correctly', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName,
    ItemTax $taxType
) {
    $data = $fixture->request()->invoice()->item()->tax()->files($fixtureName);

    $item = new Item(reference: 'reference-1');

    $item->tax($taxType);

    if ($taxType === ItemTax::EXEMPT) {
        $item->taxExemption(TaxExemptionReason::M04);
        $item->taxExemptionLaw(TaxExemptionReason::M04->laws()[0]);
    }

    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('invoice-full', [
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
