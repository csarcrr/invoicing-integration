<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use CsarCrr\InvoicingIntegration\Traits\HidesAdditionalData;
use Spatie\LaravelData\Data;

class ItemStockData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;
    use HidesAdditionalData;

    public function __construct(
        public int $itemId,
        public int $warehouseId,
        public int|float $quantity,
    ) {}
}
