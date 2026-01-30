<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use Spatie\LaravelData\Data;

class InvoiceData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    public function __construct(
        public int $id,
        public string $sequence,
        public int $total,
        public int $totalNet,
        public ?string $atcudHash = null,
        public ?Output $output = null,
    ) {}
}
