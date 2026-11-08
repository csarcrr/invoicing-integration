<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni;

use Exception;

class CouldNotFindCustomerByVatException extends Exception
{
    /** @var string */
    protected $message = 'No customer with the client VAT number was found in the provider.';
}
