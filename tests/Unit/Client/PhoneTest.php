<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;

it('has the correct phone', function (CreateClient $client, Fixtures $fixtures, string $fixtureName) {
    $data = $fixtures->request()->client()->files($fixtureName);

    $client->phone('222333444');

    expect($client->getPayload())->toMatchArray($data);
})->with('client-full', ['phone']);
