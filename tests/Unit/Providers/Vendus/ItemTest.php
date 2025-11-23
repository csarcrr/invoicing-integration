<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentItemType;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceItemIsNotValidException;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;

it('fails when item format is not valid', function () {
    $item = new stdClass;

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->type(DocumentType::Invoice);

    $resolve->buildPayload();
})->throws(InvoiceItemIsNotValidException::class);

it('has a description', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setDescription('Test Item Description');

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->first()['description'])->toBe('Test Item Description');
});

it('has a type', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setType(DocumentItemType::Service);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->first()['type_id'])->toBe('S');
});

it('has a percentage discount', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setPercentageDiscount(10);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->first()['discount_percentage'])->toBe(10);
});

it('has an amount discount', function () {
    $item = new InvoiceItem();
    $item->setReference('reference-1');
    $item->setAmountDiscount(500);

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]));

    $resolve->buildPayload();

    expect($resolve->payload()->get('items')->first()['discount_amount'])->toBe(5.0);
});
