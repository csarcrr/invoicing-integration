<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Invoices;

use Exception;

class InvoiceWithoutOutputException extends Exception
{
    /** @var string */
    protected $message = 'Invoice did not provide any output.';
}
