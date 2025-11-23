<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

it('fails when item format is not valid', function () {
    $item = new stdClass;

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice);

    $resolve->buildPayload();
})->throws(InvoiceItemIsNotValidException::class);

it('has a description', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setDescription('Test Item Description');

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->first()['description'])->toBe('Test Item Description');
});

it('has a type', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setType(DocumentItemType::Service);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->first()['type_id'])->toBe('S');
});

it('has a percentage discount', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setPercentageDiscount(10);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->first()['discount_percentage'])->toBe(10);
});

it('has an amount discount', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setAmountDiscount(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->first()['discount_amount'])->toBe(5.0);
});

it('has the correct tax applied', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setTax(DocumentItemTax::REDUCED);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->first()['tax_id'])->toBe('RED');
});

it('has applies correctly the tax exemption without law', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setTax(DocumentItemTax::EXEMPT);
    $item->setTaxExemption(TaxExemptionReason::M01);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]));

    $resolve->buildPayload();

    $data = $resolve->payload()->get('items')->first();

    expect($data['tax_exemption'])->toBe(TaxExemptionReason::M01->value);
    expect(!array_key_exists('tax_exemption_law', $data))->toBe(true);
});

it('has applies correctly the tax exemption with law', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setTax(DocumentItemTax::EXEMPT);
    $item->setTaxExemption(TaxExemptionReason::M01);
    $item->setTaxExemptionLaw(TaxExemptionReason::M01->laws()[0]);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]));

    $resolve->buildPayload();

    $data = $resolve->payload()->get('items')->first();

    expect($data['tax_exemption'])->toBe(TaxExemptionReason::M01->value);
    expect($data['tax_exemption_law'])->toBe('Artigo 16.º, n.º 6, alínea a) do CIVA');
});
