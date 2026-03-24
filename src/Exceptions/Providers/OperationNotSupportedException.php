<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers;

use Exception;

class OperationNotSupportedException extends Exception
{
    /** @var string */
    protected $message = 'This operation is not supported by the current provider.';
}
