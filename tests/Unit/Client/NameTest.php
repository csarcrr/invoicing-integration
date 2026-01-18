<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;

it('ensures invalid name do not get set', function (IntegrationProvider $provider, mixed $value, mixed $expected) {
    $client = (new ClientData)->name($value);
    expect($client->getName())->toBe($expected);
})->with('providers', [
    ['', null],
    ['💣', null],
    ['Alberto The 💣', 'Alberto The'],
]);
