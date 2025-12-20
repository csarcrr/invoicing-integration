<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\Output;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;

it('is able to set the output', function () {
    $invoiceData = new InvoiceData;
    $output = new Output(OutputFormat::PDF_BASE64, 'STRING-EXAMPLE');

    $invoiceData->setOutput($output);

    expect($invoiceData->output())->toBeInstanceOf(Output::class);
});
