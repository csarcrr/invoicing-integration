<?php

namespace CsarCrr\InvoicingIntegration;

class InvoicingClient
{
    public function __construct(
        public ?string $vat = null,
        public ?string $name = null,
    ) {}
}
