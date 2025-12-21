<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\HasData;

abstract class Base
{
    abstract public function execute(): HasData;
}
