<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;

it('is able to set the atcud hash', function () {
    $invoiceData = new InvoiceData;
    $invoiceData->setAtcudHash('STRING-EXAMPLE');

    expect($invoiceData->atcudHash())->toBe('STRING-EXAMPLE');
});
