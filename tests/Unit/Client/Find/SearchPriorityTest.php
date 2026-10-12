<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;

it('prioritizes the search criteria', function (Provider $provider, array $filters, string $vendusKey, string $expected) {
    $payload = Client::find(ClientData::from($filters))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get($vendusKey))->toBe($expected),
        Provider::MOLONI => expect($payload->get('search'))->toBe($expected),
    };
})->with('providers', [
    'vat first' => [['vat' => '123456789', 'name' => 'Name', 'id' => '1234567'], 'fiscal_id', '123456789'],
    'name second' => [['name' => 'Name', 'id' => '1234567'], 'name', 'Name'],
    'id last' => [['id' => '1234567'], 'id', '1234567'],
]);
