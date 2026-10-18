<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('prioritizes the search criteria', function (Provider $provider, array $filters, array $vendusExpected, array $moloniExpected) {
    $payload = Client::find(ClientData::from($filters))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->toArray())->toMatchArray($vendusExpected),
        Provider::MOLONI => expect($payload->except(['qty', 'offset'])->toArray())->toBe($moloniExpected),
    };
})->with('providers', [
    'vat first' => [
        ['vat' => '123456789', 'email' => 'email@test.com', 'name' => 'Name', 'id' => '1234567'],
        ['fiscal_id' => '123456789'],
        ['vat' => '123456789'],
    ],
    'email second' => [
        ['email' => 'email@test.com', 'name' => 'Name', 'id' => '1234567'],
        ['email' => 'email@test.com'],
        ['email' => 'email@test.com'],
    ],
    'number third' => [
        ['name' => 'Name', 'id' => '1234567'],
        ['name' => 'Name'],
        ['number' => '1234567'],
    ],
    'name last' => [
        ['name' => 'Name'],
        ['name' => 'Name'],
        ['name' => 'Name'],
    ],
]);
