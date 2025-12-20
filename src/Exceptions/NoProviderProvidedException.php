<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions;

use Exception;

class NoProviderProvidedException extends Exception
{
    protected $message = 'No provider was provided.';
}
