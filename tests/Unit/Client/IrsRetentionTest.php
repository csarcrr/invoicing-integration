<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;

it('has the correct irs retention', function (CreateClient $client, Fixtures $fixtures, string $fixtureName) {
    $data = $fixtures->request()->client()->files($fixtureName);

    $client->irsRetention(true);

    expect($client->getPayload())->toMatchArray($data);
})->with('client-full', ['irs_retention']);
