<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\AddressData;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use CsarCrr\InvoicingIntegration\ValueObjects\ItemData;
use CsarCrr\InvoicingIntegration\ValueObjects\TransportData;
use Illuminate\Validation\ValidationException;

it('assigns a transport to the invoice', function (Provider $provider) {
    $invoice = Invoice::create();

    $origin = AddressData::make([
        'address' => 'Rua das Flores, 125',
        'city' => 'Porto',
        'postalCode' => '4410-200',
        'country' => 'PT',
    ]);

    $destination = AddressData::make([
        'address' => 'Rua dos Paninhos, 525',
        'city' => 'Porto',
        'postalCode' => '4410-200',
        'country' => 'PT',
    ]);

    $transport = TransportData::make([
        'origin' => $origin,
        'destination' => $destination,
    ]);

    $invoice->transport($transport);

    expect($invoice->getTransport())->toBeInstanceOf(TransportData::class)
        ->and($invoice->getTransport()->origin->address)->toBe('Rua das Flores, 125')
        ->and($invoice->getTransport()->destination->address)->toBe('Rua dos Paninhos, 525');
})->with('providers');

it('has a valid payload', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->files($fixtureName);

    $invoice = Invoice::create();

    $origin = AddressData::make([
        'date' => Carbon::now()->setDay(12)->setMonth(12)->setYear(2025),
        'time' => Carbon::now()->setHour(10)->setMinute(5),
        'address' => 'Rua das Flores, 125',
        'city' => 'Porto',
        'postalCode' => '4410-200',
        'country' => 'PT',
    ]);

    $destination = AddressData::make([
        'date' => Carbon::now()->setDay(13)->setMonth(12)->setYear(2025),
        'time' => Carbon::now()->setHour(10)->setMinute(5),
        'address' => 'Rua dos Paninhos, 521',
        'city' => 'Porto',
        'postalCode' => '4410-100',
        'country' => 'PT',
    ]);

    $transport = TransportData::make([
        'origin' => $origin,
        'destination' => $destination,
        'vehicleLicensePlate' => '00-AB-00',
    ]);

    $invoice->client(ClientData::from(['name' => 'Client Name', 'vat' => 'PT123456789']));
    $invoice->item(ItemData::from(['reference' => 'reference-1']));
    $invoice->transport($transport);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['transport']);

it('fails when no client is provided with transport', function (Provider $provider) {
    $invoice = Invoice::create();

    $origin = AddressData::make([
        'address' => 'Rua das Flores, 125',
        'city' => 'Porto',
        'postalCode' => '4410-200',
        'country' => 'PT',
    ]);

    $destination = AddressData::make([
        'address' => 'Rua dos Paninhos, 521',
        'city' => 'Porto',
        'postalCode' => '4410-100',
        'country' => 'PT',
    ]);

    $transport = TransportData::make([
        'origin' => $origin,
        'destination' => $destination,
        'vehicleLicensePlate' => '00-AB-00',
    ]);

    $invoice->item(ItemData::from(['reference' => 'reference-1']));
    $invoice->transport($transport);

    $invoice->getPayload();
})->with('providers')
    ->throws(
        Exception::class,
        'ClientAction information is required when transport details are provided.'
    );

it('fails when no load date is provided with transport', function (Provider $provider) {
    $invoice = Invoice::create();
    $origin = AddressData::make([
        'address' => 'Rua das Flores, 125',
        'city' => 'Porto',
        'postalCode' => '4410-200',
        'country' => 'PT',
    ]);

    $destination = AddressData::make([
        'address' => 'Rua dos Paninhos, 521',
        'city' => 'Porto',
        'postalCode' => '4410-100',
        'country' => 'PT',
    ]);

    $transport = TransportData::make([
        'origin' => $origin,
        'destination' => $destination,
        'vehicleLicensePlate' => '00-AB-00',
    ]);

    $invoice->client(ClientData::from(['vat' => 'PT123456789', 'name' => 'Client Name']));
    $invoice->item(ItemData::from(['reference' => 'reference-1']));
    $invoice->transport($transport);

    $invoice->getPayload();
})->with('providers')->throws(NeedsDateToSetLoadPointException::class);

it('ensure invalid country throws error', function (Provider $provider) {
    $origin = AddressData::make([
        'address' => 'Rua das Flores, 125',
        'city' => 'Porto',
        'postalCode' => '4410-200',
        'country' => 'XX',
        'date' => Carbon::now()->setDay(12)->setMonth(12)->setYear(2025),
        'time' => Carbon::now()->setHour(10)->setMinute(5),
    ]);

    $destination = AddressData::make([
        'address' => 'Rua dos Paninhos, 521',
        'city' => 'Porto',
        'postalCode' => '4410-100',
        'country' => 'YY',
        'date' => Carbon::now()->setDay(13)->setMonth(12)->setYear(2025),
        'time' => Carbon::now()->setHour(10)->setMinute(5),
    ]);

    $destination->toArray();

    $transport = TransportData::make([
        'origin' => $origin,
        'destination' => $destination,
    ]);
})->with('providers')
    ->throws(ValidationException::class);
