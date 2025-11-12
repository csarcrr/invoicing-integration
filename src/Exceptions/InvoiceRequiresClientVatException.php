<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions;

use Exception;

class InvoiceRequiresClientVatException extends Exception
{
    protected $message = 'An invoice client requires a VAT number to be assigned before the invoice can be sent.';
}
