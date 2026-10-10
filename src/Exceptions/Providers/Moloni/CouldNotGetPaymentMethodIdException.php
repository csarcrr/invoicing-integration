<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni;

use Exception;

class CouldNotGetPaymentMethodIdException extends Exception
{
    /** @var string */
    protected $message = 'The chosen payment method was not found in the provider.';
}
