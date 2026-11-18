<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use CsarCrr\InvoicingIntegration\Traits\HidesAdditionalData;
use Spatie\LaravelData\Data;

class WarehouseData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;
    use HidesAdditionalData;

    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $postalCode = null,
        public ?string $country = null,
    ) {}
}
