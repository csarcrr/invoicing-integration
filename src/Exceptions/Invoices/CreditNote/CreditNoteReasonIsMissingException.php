<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Invoices\CreditNote;

use Exception;

class CreditNoteReasonIsMissingException extends Exception
{
    /** @var string */
    protected $message = 'Credit Note reason is required';
}
