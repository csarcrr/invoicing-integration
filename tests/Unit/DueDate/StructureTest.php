<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate\ShouldCreateDueDate;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate\ShouldFindDueDate;
use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\DueDate;

it('is an instance of the correct when creating a due date', function (Provider $provider) {
    $create = fn () => DueDate::create(DueDateData::make(['name' => '30 dias', 'days' => 30]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($create)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($create())->toBeInstanceOf(ShouldCreateDueDate::class),
    };
})->with('providers');

it('is an instance of the correct when finding due dates', function (Provider $provider) {
    $find = fn () => DueDate::find();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($find)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($find())->toBeInstanceOf(ShouldFindDueDate::class),
    };
})->with('providers');
