<?php

use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionCanOnlyBeUsedWithExemptTaxException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionLawCanOnlyBeUsedWithExemptionException;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

it('can assign a different tax rate', function () {
    $item = new InvoiceItem('reference-1');
    $item->setTax(DocumentItemTax::REDUCED);

    expect($item->tax())->toBe(DocumentItemTax::REDUCED);
});

it('can assign a tax exemption and a respective law', function () {
    $item = new InvoiceItem('reference-1');
    $item->setTax(DocumentItemTax::EXEMPT);
    $item->setTaxExemption(TaxExemptionReason::M01);
    $item->setTaxExemptionLaw(TaxExemptionReason::M01->laws()[0]);

    expect($item->tax())->toBe(DocumentItemTax::EXEMPT);
    expect($item->taxExemption())->toBe(TaxExemptionReason::M01);
    expect($item->taxExemptionLaw())
        ->toBeString()
        ->toBe(TaxExemptionReason::M01->laws()[0]);
});

it('throws error when assigning exemption to non-exempt defined product', function () {
    $item = new InvoiceItem('reference-1');
    $item->setTax(DocumentItemTax::REDUCED);
    $item->setTaxExemption(TaxExemptionReason::M01);
})->throws(ExemptionCanOnlyBeUsedWithExemptTaxException::class);

it('throws error when assigning exemption law to non-exempt defined product', function () {
    $item = new InvoiceItem('reference-1');
    $item->setTax(DocumentItemTax::REDUCED);
    $item->setTaxExemptionLaw(TaxExemptionReason::M01->laws()[0]);
})->throws(ExemptionLawCanOnlyBeUsedWithExemptionException::class);
