<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items;

use Exception;

class ExemptionLawCanOnlyBeUsedWithExemptionException extends Exception
{
    protected $message = 'An exemption law can only be assigned when an exemption is set.';
}
