<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers\Vendus;

use Exception;

class MissingPaymentWhenIssuingReceiptException extends Exception
{
    protected $message = 'A payment method is required when issuing a receipt.';
}
