<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionCanOnlyBeUsedWithExemptTaxException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionLawCanOnlyBeUsedWithExemptionException;
use Illuminate\Support\Collection;

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

    protected ?object $relatedDocument = null;

    /**
     * @param  string  $reference  - avoids duplicate products in some providers
     */
    public function __construct(
        protected null|int|string $reference = null,
        protected ?int $quantity = null,
    ) {
        $this->type = ItemType::Product;
        $this->quantity = $quantity ?? 1;
    }

    public function getReference(): int|string
    {
        return $this->reference;
    }

    public function getQuantity(): int
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

    public function getRelatedDocument(): ?object
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

    public function quantity(int $quantity): self
    {
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
        $this->relatedDocument = new class($documentNumber, $lineNumber) {
            public function __construct(
                public readonly string $documentId,
                public readonly int $row
            ) {}

            public function getDocumentId(): string
            {
                return $this->documentId;
            }

            public function getRow(): int
            {
                return $this->row;
            }
        };

        return $this;
    }
}
