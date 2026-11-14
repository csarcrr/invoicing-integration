<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('sends the client country', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
        'country' => 'ES',
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('country_id'))->toBe(70)
            ->and($payload->has('country'))->toBeFalse(),
        Provider::CEGID_VENDUS => expect($payload->get('country'))->toBe('ES'),
    };
})->with('providers');

it('does not send or look up the country when the client has none', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    expect($payload->has('country_id'))->toBeFalse();

    Http::assertNotSent(fn (Request $request) => Str::contains($request->url(), 'countries/getAll'));
})->with('providers');
