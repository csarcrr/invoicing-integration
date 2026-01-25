<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus;

use Exception;

class NeedsDateToSetLoadPointException extends Exception
{
    /** @var string */
    protected $message = 'A date is required to set the load point.';
}
