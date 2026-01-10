<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Invoice;

it('returns an instance of CreateInvoice', function (IntegrationProvider $provider) {
    config()->set('invoicing-integration.provider', $provider->value);

    expect(Invoice::create())->toBeInstanceOf(CreateInvoice::class);
})->with('providers');
