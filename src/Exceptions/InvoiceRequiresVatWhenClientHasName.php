<?php

namespace CsarCrr\InvoicingIntegration\Exceptions;

use Exception;

class InvoiceRequiresVatWhenClientHasName extends Exception
{
    protected $message = 'An invoice client with a name requires a VAT number to be assigned before the invoice can be sent.';
}
