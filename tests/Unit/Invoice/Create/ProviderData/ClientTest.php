<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\InvoiceRequiresClientVatException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Validation\Rules\In;

it('transforms to provider payload with all client fields', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->client()->files($fixtureName);

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
    ]);

    $invoice = Invoice::create(
        InvoiceData::make([
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'client' => $client
        ])
    );

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['complete_client']);

it('fails when vat is not valid', function (Provider $provider) {
    $invoice = Invoice::create(
        InvoiceData::make([
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'client' => ClientData::from(['vat' => '']),
        ])
    );

    $invoice->getPayload();
})->with('providers')->throws(InvoiceRequiresClientVatException::class);
