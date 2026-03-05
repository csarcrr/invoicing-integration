<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Contracts\ShouldBeUnit;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionCanOnlyBeUsedWithExemptTaxException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\ExemptionLawCanOnlyBeUsedWithExemptionException;
use CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items\UnsupportedQuantityException;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use Spatie\LaravelData\Data;

class ItemData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?int $price = null,
        public ?int $percentageDiscount = null,
        public ?int $amountDiscount = null,
        public ?string $note = null,
        public ?string $barcode = null,

        public mixed $reference = null,
        public null|int|float $quantity = 1,

        public ?ItemTax $tax = null,
        public ?TaxExemptionReason $taxExemptionReason = null,
        public ?string $taxExemptionLaw = null,

        public ?ItemType $type = ItemType::Product,
        public ?RelatedDocumentReferenceData $relatedDocument = null,
        public ?CategoryData $category = null,
        public ?ShouldBeUnit $unit = null,

        public bool $controlStock = true,
        public bool $enabled = true
    ) {
        $this->ensureValidQuantity();
        $this->ensureTaxExemptionConsistency();
        $this->ensureExemptionLawConsistency();
    }

    protected function ensureValidQuantity(): void
    {
        throw_if($this->quantity <= 0, UnsupportedQuantityException::class);
    }

    protected function ensureTaxExemptionConsistency(): void
    {
        if (is_null($this->taxExemptionReason)) {
            return;
        }

        throw_if(
            $this->tax !== ItemTax::EXEMPT,
            ExemptionCanOnlyBeUsedWithExemptTaxException::class
        );
    }

    protected function ensureExemptionLawConsistency(): void
    {
        if (is_null($this->taxExemptionLaw)) {
            return;
        }

        throw_if(
            is_null($this->taxExemptionReason),
            ExemptionLawCanOnlyBeUsedWithExemptionException::class
        );
    }
}
