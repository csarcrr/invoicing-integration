<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;

it('adds the total', function () {
    $invoiceData = new Invoice;
    $invoiceData->setTotal(1500);

    expect($invoiceData->total())->toBe(1500);
});

it('adds the total net', function () {
    $invoiceData = new Invoice;
    $invoiceData->setTotalNet(1200);

    expect($invoiceData->totalNet())->toBe(1200);
});
