<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni;

use Exception;

class CouldNotGetTaxIdException extends Exception
{
    /** @var string */
    protected $message = 'The chosen tax was not found. Make sure your config is properly configured.';
}
