<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Pagination;

use Exception;

class NoMorePagesException extends Exception
{
    /** @var string */
    protected $message = 'No more pages available to be fetched';
}
