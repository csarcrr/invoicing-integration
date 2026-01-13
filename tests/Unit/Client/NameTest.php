<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;

it('has the correct name', function (CreateClient $client, Fixtures $fixtures, string $fixtureName) {
    $data = $fixtures->request()->client()->files($fixtureName);

    $client->name('Alberto Albertino');

    expect($client->getPayload())->toMatchArray($data);
})->with('client-full', ['name']);

it('assures invalid name do not get set', function (CreateClient $client, mixed $value, mixed $expected) {
    $client->name($value);
    expect($client->getName())->toBe($expected);
})->with('client', [
    ['', null],
    ['💣', null],
    ['Alberto The 💣', 'Alberto The'],
]);
