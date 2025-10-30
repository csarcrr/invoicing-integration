<?php

namespace CsarCrr\InvoicingIntegration\Exceptions;

use Exception;

class InvoiceRequiresItemsException extends Exception
{
    protected $message = 'An invoice requires at least one item to be assigned before it can be sent.';
}
