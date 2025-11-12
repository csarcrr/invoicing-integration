<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions;

use Exception;

class InvoiceItemIsNotValidException extends Exception
{
    protected $message = 'The provided invoice item is not valid.';
}
