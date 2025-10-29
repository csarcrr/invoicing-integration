<?php

namespace CsarCrr\InvoicingIntegration;

class InvoicingClient
{
    public function __construct(
        public protected(set) string $name,
        public protected(set) string $vat,
    ) {}
}
