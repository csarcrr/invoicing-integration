<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus;

use Exception;

class CouldNotGetUnitIdException extends Exception
{
    /** @var string */
    protected $message = 'The chosen unit was not found. Make sure your config is properly configured.';
}
