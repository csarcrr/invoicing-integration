<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

it('can assign an item', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName
) {
    $data = $fixture->request()->invoice()->item()->files($fixtureName);

    $item = new Item();
    $item->reference('reference-1')
        ->quantity(2)
        ->price(1000)
        ->note('This is a test item')
        ->percentageDiscount(10)
        ->amountDiscount(50);

    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['item']);

it('can assign multiple items', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName
) {
    $data = $fixture->request()->invoice()->item()->files($fixtureName);

    $invoice->item(new Item('reference-1'));
    $invoice->item(new Item('reference-2'));

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['multiple_items']);

it('correctly applies custom taxes', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName
) {
    $data = $fixture->request()->invoice()->item()->files($fixtureName);

    $item = new Item(
        reference: 'reference-1',
    );

    $item->tax(ItemTax::EXEMPT);
    $item->taxExemption(TaxExemptionReason::M04);
    $item->taxExemptionLaw(TaxExemptionReason::M04->laws()[0]);

    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['item_tax_ise']);
