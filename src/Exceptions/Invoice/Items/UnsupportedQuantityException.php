<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items;

use Exception;

class UnsupportedQuantityException extends Exception
{
    /** @var string */
    protected $message = 'The quantity provided is not supported.';
}
