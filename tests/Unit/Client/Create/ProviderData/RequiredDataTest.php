<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\MissingClientDataException;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('fails when a field required by the provider is missing', function (Provider $provider, string $field) {
    $client = collect([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ])->except($field)->toArray();

    $payload = fn () => Client::create(ClientData::from($client))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload)->toThrow(MissingClientDataException::class),
        Provider::CEGID_VENDUS => expect($payload)->not->toThrow(Exception::class),
    };
})->with('providers', ['name', 'vat', 'address', 'city']);
