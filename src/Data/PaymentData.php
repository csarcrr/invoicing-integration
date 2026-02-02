<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use Spatie\LaravelData\Data;

class PaymentData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    public function __construct(
        public ?PaymentMethod $method = null,
        public ?int $amount = null,
    ) {}
}
