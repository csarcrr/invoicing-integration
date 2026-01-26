<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;

it('ensures invalid name do not get set', function (Provider $provider, mixed $value, mixed $expected) {
    $client = ClientData::from(['name' => $value])->toArray();

    expect($client['name'])->toBe($expected);
})->with('providers', [
    ['', null],
    ['💣', null],
    ['Alberto The 💣', 'Alberto The'],
]);
