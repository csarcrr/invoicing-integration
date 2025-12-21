<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\ValueObjects\Output;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice;

it('is able to set the output', function () {
    $invoiceData = new Invoice;
    $output = new Output(OutputFormat::PDF_BASE64, 'STRING-EXAMPLE');

    $invoiceData->setOutput($output);

    expect($invoiceData->output())->toBeInstanceOf(Output::class);
});
