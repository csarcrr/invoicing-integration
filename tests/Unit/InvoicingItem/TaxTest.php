<?php

use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionCanOnlyBeUsedWithExemptTaxException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionLawCanOnlyBeUsedWithExemptionException;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

beforeEach(function () {
    $this->item = new InvoiceItem();
});

it('can assign a different tax rate', function () {
    $this->item->setReference('reference-1');
    $this->item->setTax(DocumentItemTax::REDUCED);

    expect($this->item->tax())->toBe(DocumentItemTax::REDUCED);
});

it('can assign a tax exemption and a respective law', function () {
    $this->item->setReference('reference-1');
    $this->item->setTax(DocumentItemTax::EXEMPT);
    $this->item->setTaxExemption(TaxExemptionReason::M01);
    $this->item->setTaxExemptionLaw(TaxExemptionReason::M01->laws()[0]);

    expect($this->item->tax())->toBe(DocumentItemTax::EXEMPT);
    expect($this->item->taxExemption())->toBe(TaxExemptionReason::M01);
    expect($this->item->taxExemptionLaw())
        ->toBeString()
        ->toBe(TaxExemptionReason::M01->laws()[0]);
});

it('throws error when assigning exemption to non-exempt defined product', function () {
    $this->item->setReference('reference-1');
    $this->item->setTax(DocumentItemTax::REDUCED);
    $this->item->setTaxExemption(TaxExemptionReason::M01);
})->throws(ExemptionCanOnlyBeUsedWithExemptTaxException::class);

it('throws error when assigning exemption law to non-exempt defined product', function () {
    $this->item->setReference('reference-1');
    $this->item->setTax(DocumentItemTax::REDUCED);
    $this->item->setTaxExemptionLaw(TaxExemptionReason::M01->laws()[0]);
})->throws(ExemptionLawCanOnlyBeUsedWithExemptionException::class);
