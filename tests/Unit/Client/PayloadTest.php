<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;

it('builds the correct payload with all parameters', function (CreateClient $client, Fixtures $fixtures) {
    $data = $fixtures->request()->client()->files('client_full');

    $client
        ->name('Alberto Albertino')
        ->vat('223098091')
        ->address('Rua das Flores 125')
        ->postalCode('4100-100')
        ->city('Porto')
        ->phone('222333444')
        ->notes('Client Notes')
        ->email('alberto.albertino@aa.pt')
        ->country('PT')
        ->emailNotification(true)
        ->defaultPayDue(15)
        ->irsRetention(true);

    expect($client->getPayload()->toArray())->toMatchArray($data);
})->with('client-full');
