<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers;

use Exception;

class UnauthorizedException extends Exception
{
    protected $message = 'Unauthorized: Access is denied due to invalid credentials.';
}
