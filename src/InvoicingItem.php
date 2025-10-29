<?php

namespace CsarCrr\InvoicingIntegration;

class InvoicingItem
{
    public function __construct(
        public protected(set) string $reference,
        public protected(set) int $quantity = 1,
    ) {}
}
