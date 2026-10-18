<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use CsarCrr\InvoicingIntegration\Traits\HidesAdditionalData;
use Spatie\LaravelData\Data;

class PaymentMethodData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;
    use HidesAdditionalData;

    public function __construct(
        public string $name,
        public ?PaymentMethod $type = null,
        public ?int $id = null,
    ) {}
}
