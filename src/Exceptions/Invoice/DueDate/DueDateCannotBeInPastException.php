<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Exceptions\Invoice\DueDate;

use Exception;

class DueDateCannotBeInPastException extends Exception
{
    protected $message = 'Due date cannot be in the past.';
}
