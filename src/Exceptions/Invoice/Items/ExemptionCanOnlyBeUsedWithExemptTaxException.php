<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items;

use Exception;

class ExemptionCanOnlyBeUsedWithExemptTaxException extends Exception
{
    protected $message = 'An exemption can only be assigned to an item with exempt tax.';
}
