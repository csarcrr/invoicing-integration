<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('builds the payload when a field the provider requires is missing', function (Provider $provider, string $field) {
    $client = collect([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ])->except($field)->toArray();

    $payload = fn () => Client::create(ClientData::from($client))->getPayload();

    expect($payload)->not->toThrow(Exception::class);
})->with('providers', ['name', 'vat', 'address', 'city']);
