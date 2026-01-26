<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionCanOnlyBeUsedWithExemptTaxException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionLawCanOnlyBeUsedWithExemptionException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\UnsupportedQuantityException;

class Item
{
    protected ?int $price = null;

    protected ?int $percentageDiscount = null;

    protected ?int $amountDiscount = null;

    protected ?string $note = null;

    protected ?ItemType $type = null;

    protected ?ItemTax $tax = null;

    protected ?TaxExemptionReason $taxExemptionReason = null;

    protected ?string $taxExemptionLaw = null;

    protected ?RelatedDocumentReference $relatedDocument = null;

    public function __construct(
        protected null|int|string $reference = null,
        protected null|int|float $quantity = 1,
    ) {
        $this->type = ItemType::Product;
        $this->quantity($this->quantity);
    }

    public function getReference(): int|string
    {
        return $this->reference;
    }

    public function getQuantity(): int|float|null
    {
        return $this->quantity;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function tax(?ItemTax $tax = null): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getTax(): ?ItemTax
    {
        return $this->tax;
    }

    public function getTaxExemption(): ?TaxExemptionReason
    {
        return $this->taxExemptionReason;
    }

    /**
     * @throws ExemptionCanOnlyBeUsedWithExemptTaxException
     */
    public function taxExemption(?TaxExemptionReason $taxExemptionReason = null): self
    {
        throw_if(
            $this->getTax() !== ItemTax::EXEMPT,
            ExemptionCanOnlyBeUsedWithExemptTaxException::class
        );

        $this->taxExemptionReason = $taxExemptionReason;

        return $this;
    }

    public function getTaxExemptionLaw(): ?string
    {
        return $this->taxExemptionLaw;
    }

    /**
     * @throws ExemptionLawCanOnlyBeUsedWithExemptionException
     */
    public function taxExemptionLaw(string $taxExemptionLaw): self
    {
        throw_if(
            ! $this->taxExemptionReason,
            ExemptionLawCanOnlyBeUsedWithExemptionException::class
        );

        $this->taxExemptionLaw = $taxExemptionLaw;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function getType(): ?ItemType
    {
        return $this->type;
    }

    public function getAmountDiscount(): ?int
    {
        return $this->amountDiscount;
    }

    public function getPercentageDiscount(): ?int
    {
        return $this->percentageDiscount;
    }

    public function getRelatedDocument(): ?RelatedDocumentReference
    {
        return $this->relatedDocument;
    }

    public function amountDiscount(int $amountDiscount): self
    {
        $this->amountDiscount = $amountDiscount;

        return $this;
    }

    public function percentageDiscount(int $percentageDiscount): self
    {
        $this->percentageDiscount = $percentageDiscount;

        return $this;
    }

    public function reference(int|string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @throws UnsupportedQuantityException
     */
    public function quantity(int|float $quantity): self
    {
        throw_if($quantity <= 0, UnsupportedQuantityException::class);

        $this->quantity = $quantity;

        return $this;
    }

    public function price(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function note(string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function type(ItemType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function relatedDocument(string $documentNumber, int $lineNumber): self
    {
        $this->relatedDocument = new RelatedDocumentReference($documentNumber, $lineNumber);

        return $this;
    }
}
