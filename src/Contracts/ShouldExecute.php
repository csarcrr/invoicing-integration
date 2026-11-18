<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts;

interface ShouldExecute
{
    public function execute(): static;
}
