<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

it('can assign an item', function (CreateInvoice $invoice) {

    $item = new Item(
        reference: 'reference-1'
    );

    $invoice->item($item);

    expect($invoice->getItems()->count())->toBe(1);
    expect($invoice->getItems()->first()->getReference())->toBe('reference-1');
})->with('create-invoice');

it('has a valid payload', function (
    CreateInvoice $invoice,
    IntegrationProvider $provider,
    string $fixture
) {
    $data = Fixtures::request($provider)->invoice()->item()->files($fixture);

    $item = new Item(
        reference: 'reference-1',
        quantity: 2,
    );
    $item->price(1000);
    $item->note('This is a test item');
    $item->percentageDiscount(10);
    $item->amountDiscount(50);

    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', 'providers', ['item']);

it('correctly applies custom taxes', function (
    CreateInvoice $invoice,
    IntegrationProvider $provider,
    string $fixture
) {
    $data = Fixtures::request($provider)->invoice()->item()->files($fixture);

    $item = new Item(
        reference: 'reference-1',
    );

    $item->tax(ItemTax::EXEMPT);
    $item->taxExemption(TaxExemptionReason::M04);
    $item->taxExemptionLaw(TaxExemptionReason::M04->laws()[0]);

    $invoice->item($item);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', 'providers', ['item_tax_ise']);
