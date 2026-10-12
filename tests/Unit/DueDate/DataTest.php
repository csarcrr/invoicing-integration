<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\DueDateData;
use Illuminate\Validation\ValidationException;

it('requires a name and the number of days', function (array $data) {
    DueDateData::make($data);
})->with([
    'nothing set' => [[]],
    'missing days' => [['name' => '30 dias']],
    'missing name' => [['days' => 30]],
])->throws(ValidationException::class);

it('defaults the id to null', function () {
    $dueDate = DueDateData::make(['name' => '30 dias', 'days' => 30]);

    expect($dueDate->id)->toBeNull();
});
