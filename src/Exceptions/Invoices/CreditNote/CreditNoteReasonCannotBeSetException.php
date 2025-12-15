<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Invoices\CreditNote;

use Exception;

class CreditNoteReasonCannotBeSetException extends Exception
{
    protected $message = 'Credit note reason can only be set for credit note documents.';
}
