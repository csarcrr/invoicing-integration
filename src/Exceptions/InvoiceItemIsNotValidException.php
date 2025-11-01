<?php

namespace CsarCrr\InvoicingIntegration\Exceptions;

use Exception;

class InvoiceItemIsNotValidException extends Exception
{
    protected $message = 'The provided invoice item is not valid.';
}
