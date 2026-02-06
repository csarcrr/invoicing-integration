<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use CsarCrr\InvoicingIntegration\ValueObjects\Output;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class InvoiceData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    public function __construct(
        public Optional|int $id,
        public Optional|string $sequence,
        public Optional|int $total,
        public Optional|int $totalNet,

        public Optional|Collection $items,
        public Optional|Collection $payments,

        public ?string $atcudHash = null,
        public ?Output $output = null,
        public InvoiceType $type = InvoiceType::Invoice
    ) {}
}
