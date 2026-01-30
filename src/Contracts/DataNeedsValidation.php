<?php

namespace CsarCrr\InvoicingIntegration\Contracts;

interface DataNeedsValidation
{
    public static function make(array $data): self;
}
