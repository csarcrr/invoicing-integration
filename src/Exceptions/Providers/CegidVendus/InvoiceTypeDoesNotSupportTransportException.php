<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus;

use Exception;

class InvoiceTypeDoesNotSupportTransportException extends Exception
{
    protected $message = 'The invoice type does not support transport details.';
}
