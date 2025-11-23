<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers\Vendus;

use Exception;

class NeedsDateToSetLoadPointException extends Exception
{
    protected $message = 'A date is required to set the load point.';
}
