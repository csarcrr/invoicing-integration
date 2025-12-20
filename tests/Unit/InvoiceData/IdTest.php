<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;

it('is able to set the id', function () {
    $invoiceData = new InvoiceData;
    $invoiceData->setId(99999);

    expect($invoiceData->id())->toBe(99999);
});
