<?php

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('transforms client search filters to provider payload', function (Provider $provider, string $fixture) {
    $payload = fixtures()->request()->client()->files($fixture);

    $data = Client::find(ClientData::from([
        'id' => 1234567,
        'vat' => '123456789',
        'email' => 'email@test.com',
        'name' => 'Name',
        'reference' => 'External Reference',
        'status' => 'active',
        'date' => Carbon::createFromFormat('Y-m-d', '2026-01-01'),
    ]));

    expect($data->getPayload())->toMatchArray($payload);
})->with('providers', ['search']);

it('does not send filters the provider cannot search by', function (Provider $provider) {
    $payload = Client::find(ClientData::from([
        'reference' => '1234567',
        'city' => 'Porto',
        'address' => 'Rua das Flores 125',
        'phone' => '912345678',
        'notes' => 'Notes',
        'country' => 'PT',
        'irs_retention' => true,
    ]))->getPayload();

    expect($payload->keys()->intersect(['city', 'address', 'phone', 'notes', 'country', 'irs_retention']))->toBeEmpty();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->toArray())->not->toHaveKey('id'),
        Provider::MOLONI => expect($payload->get('number'))->toBe('1234567'),
    };
})->with('providers');

it('does not send empty filters', function (Provider $provider) {
    $payload = Client::find(ClientData::from([
        'vat' => '',
        'name' => '',
        'email' => '',
        'reference' => '',
        'status' => '',
    ]))->getPayload();

    expect($payload->keys()->intersect(['vat', 'fiscal_id', 'name', 'email', 'external_reference', 'status']))->toBeEmpty();
})->with('providers');
