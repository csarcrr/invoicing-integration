<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Exceptions\Items\UnsupportedQuantityException;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use CsarCrr\InvoicingIntegration\Traits\HidesAdditionalData;
use Spatie\LaravelData\Data;

class StockMovementData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;
    use HidesAdditionalData;

    public function __construct(
        public ?int $id = null,
        public ?int $productId = null,
        public null|int|float $quantity = null,
        public ?Carbon $date = null,
        public ?int $warehouseId = null,
        public ?string $notes = null,
    ) {
        $this->ensureNonZeroQuantity();
    }

    protected function ensureNonZeroQuantity(): void
    {
        if (is_null($this->quantity)) {
            return;
        }

        throw_if((float) $this->quantity === 0.0, UnsupportedQuantityException::class);
    }
}
