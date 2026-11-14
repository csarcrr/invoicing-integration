<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('sends the client language', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
        'language' => ['code' => 'en'],
    ]))->getPayload();

    match ($provider) {
        Provider::MOLONI => expect($payload->get('language_id'))->toBe(2),
        Provider::CEGID_VENDUS => expect($payload->has('language_id'))->toBeFalse()
            ->and($payload->has('language'))->toBeFalse(),
    };
})->with('providers');

it('does not send or look up the language when the client has none', function (Provider $provider) {
    fakeProviderDueDates($provider);

    $payload = Client::create(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->getPayload();

    expect($payload->has('language_id'))->toBeFalse();

    Http::assertNotSent(fn (Request $request) => Str::contains($request->url(), 'languages/getAll'));
})->with('providers');
