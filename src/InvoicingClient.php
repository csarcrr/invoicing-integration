<?php

namespace CsarCrr\InvoicingIntegration;

class InvoicingClient
{
    public function __construct(
        public ?string $name,
        public ?string $vat,
    ) {}
}
