<?php

namespace CsarCrr\InvoicingIntegration;

class InvoicingItem
{
    /**
     * @param string $reference - avoids duplicate products in some providers
     * @param int $quantity
     */
    public function __construct(
        public string $reference,
        public int $quantity = 1,
    ) {}

    public function setTax() {}
}
