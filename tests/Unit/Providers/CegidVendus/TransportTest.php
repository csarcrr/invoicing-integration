<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceTransportDetails;
use CsarCrr\InvoicingIntegration\InvoiceClient;

it('formats transport load point data correctly', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    $client = new InvoiceClient;
    $client->setVat('1234567890');
    $client->setName('Client Name');
    $client->setCountry('PT');
    $client->setAddress('Client Address');
    $client->setCity('Client City');
    $client->setPostalCode('4410-100');

    $transport = new InvoiceTransportDetails;
    $transport->origin()->date(now());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('PT');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setTransport($transport);
    $invoicing->setType(DocumentType::Invoice);
    $invoicing->setClient($client);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('movement_of_goods')['loadpoint'])
        ->toEqual([
            'date' => $transport->origin()->date()->format('Y-m-d'),
            'time' => $transport->origin()->time()->format('H:i'),
            'address' => $transport->origin()->address(),
            'postalcode' => $transport->origin()->postalCode(),
            'city' => $transport->origin()->city(),
            'country' => $transport->origin()->country(),
        ]);
});

it('formats transport land point data correctly', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    $client = new InvoiceClient;
    $client->setVat('1234567890');
    $client->setName('Client Name');
    $client->setCountry('PT');
    $client->setAddress('Client Address');
    $client->setCity('Client City');
    $client->setPostalCode('4410-100');

    $transport = new InvoiceTransportDetails;
    $transport->origin()->date(now());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('PT');

    $transport->destination()->date(now()->addDay());
    $transport->destination()->time(now()->addDay());
    $transport->destination()->address('123 Main St');
    $transport->destination()->city('Cityville');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('PT');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setTransport($transport);
    $invoicing->setType(DocumentType::Invoice);
    $invoicing->setClient($client);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('movement_of_goods')['landpoint'])
        ->toEqual([
            'date' => $transport->destination()->date()->format('Y-m-d'),
            'time' => $transport->destination()->time()->format('H:i'),
            'address' => $transport->destination()->address(),
            'postalcode' => $transport->destination()->postalCode(),
            'city' => $transport->destination()->city(),
            'country' => $transport->destination()->country(),
        ]);
});

it('formats transport vehicle license plate correctly', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    $client = new InvoiceClient;
    $client->setVat('1234567890');
    $client->setName('Client Name');
    $client->setCountry('PT');
    $client->setAddress('Client Address');
    $client->setCity('Client City');
    $client->setPostalCode('4410-100');

    $transport = new InvoiceTransportDetails;

    $transport->vehicleLicensePlate('ABC-1234');
    $transport->origin()->date(now());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('PT');

    $transport->destination()->date(now()->addDay());
    $transport->destination()->time(now()->addDay());
    $transport->destination()->address('123 Main St');
    $transport->destination()->city('Cityville');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('PT');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setTransport($transport);
    $invoicing->setType(DocumentType::Invoice);
    $invoicing->setClient($client);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();

    expect($resolve->payload()->get('movement_of_goods')['vehicle_id'])
        ->toEqual('ABC-1234');
});

it('fails when no date is set for load point', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    $client = new InvoiceClient;
    $client->setVat('1234567890');
    $client->setName('Client Name');
    $client->setCountry('PT');
    $client->setAddress('Client Address');
    $client->setCity('Client City');
    $client->setPostalCode('4410-100');

    $transport = new InvoiceTransportDetails;
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('PT');

    $transport->destination()->date(now()->addDay());
    $transport->destination()->time(now()->addDay());
    $transport->destination()->address('123 Main St');
    $transport->destination()->city('Cityville');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('PT');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setTransport($transport);
    $invoicing->setType(DocumentType::Invoice);
    $invoicing->setClient($client);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();
})->throws(NeedsDateToSetLoadPointException::class);

it('fails when setting an invalid country on origin', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    $client = new InvoiceClient;
    $client->setVat('1234567890');
    $client->setName('Client Name');
    $client->setCountry('PT');
    $client->setAddress('Client Address');
    $client->setCity('Client City');
    $client->setPostalCode('4410-100');

    $transport = new InvoiceTransportDetails;
    $transport->origin()->date(now());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('BAD COUNTRY');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setTransport($transport);
    $invoicing->setType(DocumentType::Invoice);
    $invoicing->setClient($client);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();
})->throws(InvalidCountryException::class);

it('fails when setting an invalid country on destination', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    $client = new InvoiceClient;
    $client->setVat('1234567890');
    $client->setName('Client Name');
    $client->setCountry('PT');
    $client->setAddress('Client Address');
    $client->setCity('Client City');
    $client->setPostalCode('4410-100');

    $transport = new InvoiceTransportDetails;

    $transport->destination()->date(now()->addDay());
    $transport->destination()->time(now()->addDay());
    $transport->destination()->address('123 Main St');
    $transport->destination()->city('Cityville');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('BAD COUNTRY');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setTransport($transport);
    $invoicing->setType(DocumentType::Invoice);
    $invoicing->setClient($client);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();
})->throws(InvalidCountryException::class);

it('fails when no client is set and transport details are provided', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    $transport = new InvoiceTransportDetails;
    $transport->origin()->date(now());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('PT');

    $transport->destination()->date(now()->addDay());
    $transport->destination()->time(now()->addDay());
    $transport->destination()->address('123 Main St');
    $transport->destination()->city('Cityville');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('PT');

    $invoicing = Invoice::create();
    $invoicing->addItem($item);
    $invoicing->setTransport($transport);
    $invoicing->setType(DocumentType::Invoice);

    $resolve = app(config('invoicing-integration.provider'), [
        'invoicing' => $invoicing,
    ]);

    $resolve->create();
})->throws(Exception::class, 'Client information is required when transport details are provided.');
