<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Client;
use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;

it('builds the correct payload with all parameters', function (IntegrationProvider $provider) {
    $data = fixtures()->request()->client()->files('client_full');

    $client = (new ClientData)->name('Alberto Albertino')
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

    $create = Client::create($client);

    expect($create->getPayload()->toArray())->toMatchArray($data);
})->with('providers');
