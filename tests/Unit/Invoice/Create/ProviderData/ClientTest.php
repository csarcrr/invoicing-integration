<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\DueDateTerm;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotFindCustomerByVatException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('transforms to provider payload with all client fields', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->client()->files($fixtureName);

    fakeProviderClientByVat($provider, [['customer_id' => 12001, 'vat' => '123456789']]);

    $client = ClientData::from([
        'name' => 'John Doe',
        'vat' => '123456789',
        'address' => 'Rua das Flores 125',
        'city' => 'Porto',
        'postalCode' => '4410-000',
        'country' => 'PT',
        'email' => 'john.doe@mail.com',
        'phone' => '220123123',
        'irsRetention' => true,
        'emailNotification' => true,
    ]);

    $invoice = Invoice::create(
        InvoiceData::make([
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'client' => $client,
        ])
    );

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['complete_client']);

it('sends only the client fields the provider accepts on a document', function (Provider $provider) {
    $client = ClientData::from([
        'id' => '12001',
        'name' => 'John Doe',
        'vat' => '123456789',
        'email' => 'john.doe@mail.com',
        'country' => 'PT',
        'city' => 'Porto',
        'address' => 'Rua das Flores 125',
        'postalCode' => '4410-000',
        'phone' => '220123123',
        'notes' => 'VIP client',
        'defaultPayDue' => DueDateTerm::Days30,
        'discount' => 5.0,
        'creditLimit' => 1000.0,
        'deliveryMethodId' => 1,
        'reference' => 'EXT-1',
        'status' => 'active',
        'emailNotification' => true,
        'irsRetention' => true,
        'date' => '2025-06-15',
    ]);

    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'client' => $client,
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('client'))->toEqual([
            'id' => '12001',
            'name' => 'John Doe',
            'fiscal_id' => '123456789',
            'email' => 'john.doe@mail.com',
            'country' => 'PT',
            'city' => 'Porto',
            'address' => 'Rua das Flores 125',
            'postalcode' => '4410-000',
            'phone' => '220123123',
            'notes' => 'VIP client',
            'external_reference' => 'EXT-1',
            'irs_retention' => 'yes',
            'send_email' => 'yes',
        ]),
        Provider::MOLONI => expect($payload->get('customer_id'))->toBe(12001),
    };
})->with('providers');

it('fails when vat is not valid', function (Provider $provider) {
    $invoice = Invoice::create(
        InvoiceData::make([
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'client' => ClientData::from(['vat' => '']),
        ])
    );

    $invoice->getPayload();
})->with('providers')->throws(InvoiceRequiresClientVatException::class);

it('sends the client id to the provider', function (Provider $provider) {
    $payload = Invoice::create(
        InvoiceData::make([
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'client' => ClientData::from(['id' => '12001', 'vat' => '123456789']),
        ])
    )->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('client')['id'])->toBe('12001'),
        Provider::MOLONI => expect($payload->get('customer_id'))->toBe(12001),
    };
})->with('providers');

it('sends the client found by the client vat', function (Provider $provider) {
    $clients = fixtures()->response()->client()->files('response_multiple');

    fakeProviderClientByVat($provider, $clients);

    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'client' => ClientData::from(['vat' => '215783920']),
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('client')['fiscal_id'])->toBe('215783920'),
        Provider::MOLONI => expect($payload->get('customer_id'))->toBe($clients[0]['customer_id'])
            ->and(Http::recorded(fn (Request $request) => Str::contains($request->url(), 'customers/getByVat')
                && Str::contains($request->body(), 'vat=215783920')))->toHaveCount(1),
    };
})->with('providers');

it('sends the client whose vat matches the client vat exactly', function (Provider $provider) {
    fakeProviderClientByVat($provider, [
        ['customer_id' => 12010, 'vat' => '2157839201'],
        ['customer_id' => 12001, 'vat' => '215783920'],
    ]);

    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'client' => ClientData::from(['vat' => '215783920']),
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('client')['fiscal_id'])->toBe('215783920'),
        Provider::MOLONI => expect($payload->get('customer_id'))->toBe(12001),
    };
})->with('providers');

it('fails when no client has the client vat', function (Provider $provider) {
    fakeProviderClientByVat($provider, []);

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'client' => ClientData::from(['vat' => '215783920']),
    ]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($invoice->getPayload()->get('client')['fiscal_id'])->toBe('215783920'),
        Provider::MOLONI => expect(fn () => $invoice->getPayload())->toThrow(CouldNotFindCustomerByVatException::class),
    };
})->with('providers');

it('fails when only clients with a different vat contain the client vat', function (Provider $provider) {
    fakeProviderClientByVat($provider, [['customer_id' => 12010, 'vat' => '2157839201']]);

    $invoice = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'client' => ClientData::from(['vat' => '215783920']),
    ]));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($invoice->getPayload()->get('client')['fiscal_id'])->toBe('215783920'),
        Provider::MOLONI => expect(fn () => $invoice->getPayload())->toThrow(CouldNotFindCustomerByVatException::class),
    };
})->with('providers');

it('falls back to the configured client when no client is set', function (Provider $provider) {
    $payload = Invoice::create(
        InvoiceData::make([
            'items' => [ItemData::from(['reference' => 'reference-1'])],
        ])
    )->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->has('client'))->toBeFalse(),
        Provider::MOLONI => expect($payload->get('customer_id'))->toBe(999999),
    };
})->with('providers');
