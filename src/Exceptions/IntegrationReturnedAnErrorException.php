<?php

namespace CsarCrr\InvoicingIntegration\Exceptions;

use Exception;

class IntegrationReturnedAnErrorException extends Exception
{
    protected $message = 'Integration returned an error.';
}
