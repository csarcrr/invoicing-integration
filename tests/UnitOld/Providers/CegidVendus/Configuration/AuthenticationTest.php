<?php

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Providers\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;

it('properly sets the correct auth header', function () {
    $invoice = Invoice::create();
    $invoice->addItem(new Item('bb'));

    $object = Provider::resolve()->invoice()->create($invoice);
    $reflectionClass = new ReflectionClass($object);
    $property = $reflectionClass->getProperty('headers');
    $value = $property->getValue($object);

    expect($value)->toBeArray()
        ->toHaveKey('Authorization')
        ->and($value['Authorization'])
        ->toBe('Bearer 1234');
});
