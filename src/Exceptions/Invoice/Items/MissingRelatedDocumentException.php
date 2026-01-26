<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Invoice\Items;

use Exception;

class MissingRelatedDocumentException extends Exception
{
    /** @var string */
    protected $message = 'A related document is missing for the item.';
}
