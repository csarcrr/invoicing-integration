<?php

namespace CsarCrr\InvoicingIntegration\Contracts;

interface DataNeedsValidation
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data): self;
}
