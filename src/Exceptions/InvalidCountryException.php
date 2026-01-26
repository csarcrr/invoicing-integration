<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions;

use Exception;

class InvalidCountryException extends Exception
{
    /** @var string */
    protected $message = 'The provided country is not valid.';
}
