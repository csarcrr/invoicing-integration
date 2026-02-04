<?php

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('it builds the correct payload', function (Provider $provider, string $fixture) {
    $payload = fixtures()->request()->client()->files($fixture);

    $data = Client::find(ClientData::from([
            'id' => 1234567,
            'vat' => '123456789',
            'email' => 'email@test.com',
            'name' => 'Name',
            'external_reference' => 'External Reference',
            'status' => 'active',
            'date' => Carbon::createFromFormat('Y-m-d', '2026-01-01'),
    ]));

    expect($data->getPayload())->toMatchArray($payload);
})->with('providers', ['search'])->only();
