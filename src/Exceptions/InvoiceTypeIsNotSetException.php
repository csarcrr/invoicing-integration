<?php

namespace CsarCrr\InvoicingIntegration\Exceptions;

use Exception;

class InvoiceTypeIsNotSetException extends Exception
{
    protected $message = 'The invoice type is not set.';
}
