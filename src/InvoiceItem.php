<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Enums\Tax\DocumentItemTax;
use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionLawCanOnlyBeUsedWithExemptionException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionCanOnlyBeUsedWithExemptTaxException;

class InvoiceItem
{
    protected ?int $price = null;
    protected ?string $description = null;
    protected DocumentItemType $type;
    protected DocumentItemTax $tax;
    protected ?TaxExemptionReason $taxExemptionReason = null;
    protected ?string $taxExemptionLaw = null;

    /**
     * @param  string  $reference  - avoids duplicate products in some providers
     */
    public function __construct(
        protected int|string $reference,
        protected int $quantity = 1,
    ) {
        $this->type = DocumentItemType::Product;
    }

    public function reference(): int|string
    {
        return $this->reference;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function tax(?DocumentItemTax $tax = null): DocumentItemTax|self
    {
        return $this->tax ?? $this->tax = $tax;
    }

    public function taxExemption(?TaxExemptionReason $taxExemptionReason = null): TaxExemptionReason|self
    {
        throw_if(
            $this->tax() !== DocumentItemTax::EXEMPT,
            ExemptionCanOnlyBeUsedWithExemptTaxException::class
        );

        return $this->taxExemptionReason ?? $this->taxExemptionReason = $taxExemptionReason;
    }

    public function taxExemptionLaw(?string $taxExemptionLaw = null): string|self
    {
        throw_if(
            !$this->taxExemptionReason,
            ExemptionLawCanOnlyBeUsedWithExemptionException::class
        );

        return $this->taxExemptionLaw ?? $this->taxExemptionLaw = $taxExemptionLaw;
    }

    public function price(): ?int
    {
        return $this->price;
    }

    public function type(): DocumentItemType
    {
        return $this->type;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setType(DocumentItemType $type): self
    {
        $this->type = $type;

        return $this;
    }
}
