<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\DueDate;

it('transforms due date data to provider payload', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support creating due dates.');
    }

    expect(DueDate::create(DueDateData::make(['name' => '30 dias', 'days' => 30]))->getPayload()->toArray())
        ->toMatchArray(fixtures()->request()->dueDate()->files('create'));
})->with('providers');
