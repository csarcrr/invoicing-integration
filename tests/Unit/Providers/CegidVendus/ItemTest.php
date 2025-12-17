<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoiceClient;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new InvoiceItem();
    $this->client = new InvoiceClient();
});

it('has a description', function () {
    $this->item->setReference('reference-1');
    $this->item->setNote('Test Item note');

    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items')->first()['text'])->toBe('Test Item note');
});

it('has a type', function () {
    $this->item->setReference('reference-1');
    $this->item->setType(DocumentItemType::Service);

    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items')->first()['type_id'])->toBe('S');
});

it('has a percentage discount', function () {
    $this->item->setReference('reference-1');
    $this->item->setPercentageDiscount(10);

    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items')->first()['discount_percentage'])->toBe(10);
});

it('has an amount discount', function () {
    $this->item->setReference('reference-1');
    $this->item->setAmountDiscount(500);

    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items')->first()['discount_amount'])->toBe(5.0);
});

it('has the correct tax applied', function () {
    $this->item->setReference('reference-1');
    $this->item->setTax(DocumentItemTax::REDUCED);

    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('items')->first()['tax_id'])->toBe('RED');
});

it('has applies correctly the tax exemption without law', function () {
    $this->item->setReference('reference-1');
    $this->item->setTax(DocumentItemTax::EXEMPT);
    $this->item->setTaxExemption(TaxExemptionReason::M01);

    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    $data = $resolve->payload()->get('items')->first();

    expect($data['tax_exemption'])->toBe(TaxExemptionReason::M01->value);
    expect(! array_key_exists('tax_exemption_law', $data))->toBe(true);
});

it('has applies correctly the tax exemption with law', function () {
    $this->item->setReference('reference-1');
    $this->item->setTax(DocumentItemTax::EXEMPT);
    $this->item->setTaxExemption(TaxExemptionReason::M01);
    $this->item->setTaxExemptionLaw(TaxExemptionReason::M01->laws()[0]);

    $this->invoice->addItem($this->item);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $this->invoice,
    ]);

    $resolve->create();

    $data = $resolve->payload()->get('items')->first();

    expect($data['tax_exemption'])->toBe(TaxExemptionReason::M01->value);
    expect($data['tax_exemption_law'])->toBe('Artigo 16.º, n.º 6, alínea a) do CIVA');
});
