<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;

it('ensures invalid name do not get set', function (IntegrationProvider $provider, Fixtures $fixtures, mixed $value, mixed $expected) {
    $client = (new ClientData)->name($value);
    expect($client->getName())->toBe($expected);
})->with('client-full', [
    ['', null],
    ['💣', null],
    ['Alberto The 💣', 'Alberto The'],
]);
