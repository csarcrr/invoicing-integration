<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions\Invoice;

use CsarCrr\InvoicingIntegration\Actions\Invoice\Create\Create;

final class Invoice
{
    public function create(): Create
    {
        return new Create;
    }

    // public function get (): Get {
    //     return new Get();
    // }
}
