<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni;

use Exception;

class MissingCategoryException extends Exception
{
    /** @var string */
    protected $message = 'Moloni requires a category to create an item.';
}
