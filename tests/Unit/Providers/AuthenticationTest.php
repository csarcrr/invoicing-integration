<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;

it('properly sets the auth for '.IntegrationProvider::CEGID_VENDUS->value, function () {
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
})->todo();
