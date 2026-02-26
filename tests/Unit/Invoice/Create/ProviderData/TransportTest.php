<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\AddressData;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\TransportData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Validation\ValidationException;

it('transforms to provider payload with transport details', function (Provider $provider, string $fixtureName) {
    $data = fixtures()->request()->invoice()->files($fixtureName);

    $origin = AddressData::make([
        'dateTime' => Carbon::now()->setDay(12)->setMonth(12)->setYear(2025)->setHour(10)->setMinute(5),
        'address' => 'Rua das Flores, 125',
        'city' => 'Porto',
        'postalCode' => '4410-200',
        'country' => 'PT',
    ]);

    $destination = AddressData::make([
        'dateTime' => Carbon::now()->setDay(13)->setMonth(12)->setYear(2025)->setHour(10)->setMinute(5),
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

    $invoice = Invoice::create(
        InvoiceData::make([
            'client' => ClientData::from(['name' => 'Client Name', 'vat' => '123456789']),
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'transport' => $transport,
        ])
    );

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['transport']);

it('fails when no client is provided with transport', function (Provider $provider) {
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

    $invoice = Invoice::create(
        InvoiceData::make([
            'transport' => $transport,
            'items' => [ItemData::from(['reference' => 'reference-1'])],
        ])
    );

    $invoice->getPayload();
})->with('providers')
    ->throws(
        Exception::class,
        'Client information is required when transport details are provided.'
    );

it('fails when no load date is provided with transport', function (Provider $provider) {
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

    $invoice = Invoice::create(
        InvoiceData::make([
            'transport' => $transport,
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'client' => ClientData::from(['vat' => '123456789', 'name' => 'Client Name']),
        ])
    );

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

    TransportData::make([
        'origin' => $origin,
        'destination' => $destination,
    ]);
})->with('providers')
    ->throws(ValidationException::class);
