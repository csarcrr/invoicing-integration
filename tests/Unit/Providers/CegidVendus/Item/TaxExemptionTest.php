<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

beforeEach(function () {
    $this->invoice = Invoice::create();
    $this->item = new Item;
    $this->client = new Client;
});

it('applies correctly the tax exemption with law', function () {
    $this->item->setReference('reference-1');
    $this->item->setTax(DocumentItemTax::EXEMPT);
    $this->item->setTaxExemption(TaxExemptionReason::M01);
    $this->item->setTaxExemptionLaw(TaxExemptionReason::M01->laws()[0]);

    $this->invoice->addItem($this->item);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    $data = $resolve->payload()->get('items')->first();

    expect($data['tax_exemption'])->toBe(TaxExemptionReason::M01->value);
    expect($data['tax_exemption_law'])->toBe('Artigo 16.º, n.º 6, alínea a) do CIVA');
});

it('applies correctly the tax exemption without law', function () {
    $this->item->setReference('reference-1');
    $this->item->setTax(DocumentItemTax::EXEMPT);
    $this->item->setTaxExemption(TaxExemptionReason::M01);

    $this->invoice->addItem($this->item);

    $resolve = Provider::resolve()->invoice()->create($this->invoice);

    $data = $resolve->payload()->get('items')->first();

    expect($data['tax_exemption'])->toBe(TaxExemptionReason::M01->value);
    expect(! array_key_exists('tax_exemption_law', $data))->toBe(true);
});
