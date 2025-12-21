<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;

it('is able to set the atcud hash', function () {
    $invoiceData = new Invoice;
    $invoiceData->setAtcudHash('STRING-EXAMPLE');

    expect($invoiceData->atcudHash())->toBe('STRING-EXAMPLE');
});
