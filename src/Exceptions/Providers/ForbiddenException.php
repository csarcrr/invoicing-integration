<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers;

use Exception;

class ForbiddenException extends Exception
{
    /** @var string */
    protected $message = 'Forbidden';
}
