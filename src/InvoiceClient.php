<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

class InvoiceClient
{
    public function __construct(
        public ?string $vat = null,
        public ?string $name = null,
    ) {}
}
