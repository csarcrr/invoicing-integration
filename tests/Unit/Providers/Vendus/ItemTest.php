<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;

it('fails when item format is not valid', function () {
    $item = new stdClass;

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice);

    $resolve->buildPayload();
})->throws(InvoiceItemIsNotValidException::class);
