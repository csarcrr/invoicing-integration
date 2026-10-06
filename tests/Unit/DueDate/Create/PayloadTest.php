<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\DueDate;

it('transforms due date data to provider payload', function (Provider $provider) {
    $create = fn () => DueDate::create(DueDateData::make(['name' => '30 dias', 'days' => 30]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($create)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($create()->getPayload()->toArray())
            ->toMatchArray(fixtures()->request()->dueDate()->files('create')),
    };
})->with('providers');
