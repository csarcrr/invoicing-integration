<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Exceptions\Invoices\InvoiceWithoutOutputException;
use Spatie\LaravelData\Data;

class InvoiceData extends Data
{
    public function __construct(
        public int $id,
        public string $sequence,
        public int $total,
        public int $totalNet,
        public ?string $atcudHash = null,
        public ?Output $output = null,
    ) {}
}
