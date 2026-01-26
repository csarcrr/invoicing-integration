<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions;

use Exception;

class InvoiceRequiresVatWhenClientHasName extends Exception
{
    /** @var string */
    protected $message = 'An invoice client with a name requires a VAT number to be assigned before the invoice can be sent.';
}
