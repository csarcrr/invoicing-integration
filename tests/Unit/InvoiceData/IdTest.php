<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;

it('is able to set the id', function () {
    $invoiceData = new Invoice;
    $invoiceData->setId(99999);

    expect($invoiceData->id())->toBe(99999);
});
