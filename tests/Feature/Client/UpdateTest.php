<?php

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Client;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('returns the updated client', function (Provider $provider, string $responseFixture) {
    fakeProviderDueDates($provider);

    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    $data = Client::update(ClientData::from([
        'id' => '99999',
        'reference' => 'CLIENT-1',
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->execute()->getClient();

    expect($data->id)->toBe('99999')
        ->and($data->name)->toBe('Alberto Albertino')
        ->and($data->getAdditionalData())->not->toBeEmpty();
})->with([
    'vendus' => [fn () => cegidVendusProvider(), 'response'],
    'moloni' => [fn () => moloniProvider(), 'response_simple'],
]);

test('sends the update to the provider', function (Provider $provider, string $responseFixture) {
    fakeProviderDueDates($provider);

    Http::fake(mockResponse(fixtures()->response()->client()->files($responseFixture)));

    Client::update(ClientData::from([
        'id' => '99999',
        'reference' => 'CLIENT-1',
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->execute();

    Http::assertSent(match ($provider) {
        Provider::CEGID_VENDUS => fn (Request $request) => $request->method() === 'PATCH'
            && Str::endsWith($request->url(), 'clients/99999'),
        Provider::MOLONI => fn (Request $request) => Str::contains($request->url(), 'customers/update')
            && Str::contains($request->body(), 'customer_id=99999')
            && Str::contains($request->body(), 'number=CLIENT-1')
            && Str::contains($request->body(), 'company_id=123456'),
    });
})->with([
    'vendus' => [fn () => cegidVendusProvider(), 'response'],
    'moloni' => [fn () => moloniProvider(), 'response_simple'],
]);

test('fails when the client has no id', function (Provider $provider) {
    fakeProviderDueDates($provider);

    Client::update(ClientData::from([
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->execute();
})->with('providers')->throws(InvalidArgumentException::class, 'Client ID is required.');

test('fails when the provider rejects the update', function (Provider $provider) {
    fakeProviderDueDates($provider);

    Http::fake(match ($provider) {
        Provider::CEGID_VENDUS => mockResponse([
            'errors' => [
                ['code' => 'A001', 'message' => 'NIF em duplicado, já existe um registo associado ao NIF indicado'],
            ],
        ], 400),
        Provider::MOLONI => mockResponse([
            ['code' => '4 vat', 'description' => "Field 'vat' must be unique"],
        ]),
    });

    expect(fn () => Client::update(ClientData::from([
        'id' => '99999',
        'reference' => 'CLIENT-1',
        'name' => 'Alberto Albertino',
        'vat' => '223098091',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
    ]))->execute())->toThrow(RequestFailedException::class, match ($provider) {
        Provider::CEGID_VENDUS => 'A001 - NIF em duplicado, já existe um registo associado ao NIF indicado',
        Provider::MOLONI => "4 vat - Field 'vat' must be unique",
    });
})->with('providers');
