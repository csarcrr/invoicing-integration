<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni;

use Exception;

class CouldNotGetCountryIdException extends Exception
{
    /** @var string */
    protected $message = 'The chosen country was not found in the provider.';
}
