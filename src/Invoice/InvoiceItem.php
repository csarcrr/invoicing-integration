<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Invoice;

use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionCanOnlyBeUsedWithExemptTaxException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionLawCanOnlyBeUsedWithExemptionException;

class InvoiceItem
{
    protected ?int $price = null;

    protected ?int $percentageDiscount = null;

    protected ?int $amountDiscount = null;

    protected ?string $note = null;

    protected ?DocumentItemType $type = null;

    protected ?DocumentItemTax $tax = null;

    protected ?TaxExemptionReason $taxExemptionReason = null;

    protected ?string $taxExemptionLaw = null;

    /**
     * @param  string  $reference  - avoids duplicate products in some providers
     */
    public function __construct(
        protected null|int|string $reference = null,
        protected ?int $quantity = null,
    ) {
        $this->type = DocumentItemType::Product;
        $this->quantity = $quantity ?? 1;
    }

    public function reference(): int|string
    {
        return $this->reference;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function note(): ?string
    {
        return $this->note;
    }

    public function setTax(?DocumentItemTax $tax = null): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function tax(): ?DocumentItemTax
    {
        return $this->tax;
    }

    public function taxExemption(): ?TaxExemptionReason
    {
        return $this->taxExemptionReason;
    }

    public function setTaxExemption(?TaxExemptionReason $taxExemptionReason = null): self
    {
        throw_if(
            $this->tax() !== DocumentItemTax::EXEMPT,
            ExemptionCanOnlyBeUsedWithExemptTaxException::class
        );

        $this->taxExemptionReason = $taxExemptionReason;

        return $this;
    }

    public function taxExemptionLaw(): ?string
    {
        return $this->taxExemptionLaw;
    }

    public function setTaxExemptionLaw(string $taxExemptionLaw): self
    {
        throw_if(
            ! $this->taxExemptionReason,
            ExemptionLawCanOnlyBeUsedWithExemptionException::class
        );

        $this->taxExemptionLaw = $taxExemptionLaw;

        return $this;
    }

    public function price(): ?int
    {
        return $this->price;
    }

    public function type(): ?DocumentItemType
    {
        return $this->type;
    }

    public function amountDiscount(): ?int
    {
        return $this->amountDiscount;
    }

    public function percentageDiscount(): ?int
    {
        return $this->percentageDiscount;
    }

    public function setAmountDiscount(int $amountDiscount): self
    {
        $this->amountDiscount = $amountDiscount;

        return $this;
    }

    public function setPercentageDiscount(int $percentageDiscount): self
    {
        $this->percentageDiscount = $percentageDiscount;

        return $this;
    }

    public function setReference(int|string $reference): void
    {
        $this->reference = $reference;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function setNote(string $note): void
    {
        $this->note = $note;
    }

    public function setType(DocumentItemType $type): self
    {
        $this->type = $type;

        return $this;
    }

}
