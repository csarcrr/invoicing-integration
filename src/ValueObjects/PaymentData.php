<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use Spatie\LaravelData\Data;

class PaymentData extends Data
{
    public function __construct(
        public ?PaymentMethod $method = null,
        public ?int $amount = null,
    ) {}
}
