<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;

it('has the correct notes', function (CreateClient $client, Fixtures $fixtures, string $fixtureName) {
    $data = $fixtures->request()->client()->files($fixtureName);

    $client->notes('Client Notes');

    expect($client->getPayload())->toMatchArray($data);
})->with('client-full', ['notes']);
