<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;

it('ensures invalid name do not get set', function (Provider $provider, mixed $value, mixed $expected) {
    $client = ClientData::from(['name' => $value])->toArray();

    expect($client['name'])->toBe($expected);
})->with('providers', [
    ['', null],
    ['💣', null],
    ['Alberto The 💣', 'Alberto The'],
]);
