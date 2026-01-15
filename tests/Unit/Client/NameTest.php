<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;

it('ensures invalid name do not get set', function (CreateClient $client, mixed $value, mixed $expected) {
    $client->name($value);
    expect($client->getName())->toBe($expected);
})->with('client', [
    ['', null],
    ['💣', null],
    ['Alberto The 💣', 'Alberto The'],
]);
