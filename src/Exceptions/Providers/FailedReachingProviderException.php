<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers;

use Exception;

class FailedReachingProviderException extends Exception
{
    protected $message = 'Failed to reach the provider: The request could not be completed.';
}
