<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;

it('is able to set the sequence', function () {
    $invoiceData = new Invoice;
    $invoiceData->setSequence('STRING-EXAMPLE');

    expect($invoiceData->sequence())->toBe('STRING-EXAMPLE');
});

it('fails when trying to set an integer as sequence', function () {
    $invoiceData = new Invoice;

    $invoiceData->setSequence(1234);
})->throws(TypeError::class);
