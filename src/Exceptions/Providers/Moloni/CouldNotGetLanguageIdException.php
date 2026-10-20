<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni;

use Exception;

class CouldNotGetLanguageIdException extends Exception
{
    /** @var string */
    protected $message = 'The chosen language was not found in the provider.';
}
