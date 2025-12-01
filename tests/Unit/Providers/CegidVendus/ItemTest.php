<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

it('has a description', function () {
    $item = new InvoiceItem;
    $item->setReference('reference-1');
    $item->setNote('Test Item note');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items')->first()['text'])->toBe('Test Item note');
});

it('has a type', function () {
    $item = new InvoiceItem;
    $item->setReference('reference-1');
    $item->setType(DocumentItemType::Service);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items')->first()['type_id'])->toBe('S');
});

it('has a percentage discount', function () {
    $item = new InvoiceItem;
    $item->setReference('reference-1');
    $item->setPercentageDiscount(10);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items')->first()['discount_percentage'])->toBe(10);
});

it('has an amount discount', function () {
    $item = new InvoiceItem;
    $item->setReference('reference-1');
    $item->setAmountDiscount(500);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items')->first()['discount_amount'])->toBe(5.0);
});

it('has the correct tax applied', function () {
    $item = new InvoiceItem;
    $item->setReference('reference-1');
    $item->setTax(DocumentItemTax::REDUCED);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items')->first()['tax_id'])->toBe('RED');
});

it('has applies correctly the tax exemption without law', function () {
    $item = new InvoiceItem;
    $item->setReference('reference-1');
    $item->setTax(DocumentItemTax::EXEMPT);
    $item->setTaxExemption(TaxExemptionReason::M01);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    $data = $resolve->payload()->get('items')->first();

    expect($data['tax_exemption'])->toBe(TaxExemptionReason::M01->value);
    expect(! array_key_exists('tax_exemption_law', $data))->toBe(true);
});

it('has applies correctly the tax exemption with law', function () {
    $item = new InvoiceItem;
    $item->setReference('reference-1');
    $item->setTax(DocumentItemTax::EXEMPT);
    $item->setTaxExemption(TaxExemptionReason::M01);
    $item->setTaxExemptionLaw(TaxExemptionReason::M01->laws()[0]);

    $invoicing = Invoice::create();
    $invoicing->addItem($item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    $data = $resolve->payload()->get('items')->first();

    expect($data['tax_exemption'])->toBe(TaxExemptionReason::M01->value);
    expect($data['tax_exemption_law'])->toBe('Artigo 16.º, n.º 6, alínea a) do CIVA');
});
