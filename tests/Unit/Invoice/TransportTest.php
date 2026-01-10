<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\Tests\Fixtures\Fixtures;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\TransportDetails;

it('assigns a transport to the invoice', function (CreateInvoice $invoice, Fixtures $fixture) {
    $transport = new TransportDetails;

    $transport->origin()
        ->address('Rua das Flores, 125')
        ->city('Porto')
        ->postalCode('4410-200')
        ->country('PT');

    $transport->destination()
        ->address('Rua dos Paninhos, 521')
        ->city('Porto')
        ->postalCode('4410-100')
        ->country('PT');

    $invoice->transport($transport);

    expect($invoice->getTransport())->toBeInstanceOf(TransportDetails::class);
    expect($invoice->getTransport()->origin()->getAddress())->toBe('Rua das Flores, 125');
    expect($invoice->getTransport()->destination()->getAddress())->toBe('Rua dos Paninhos, 521');
})->with('create-invoice');

it('has a valid payload', function (
    CreateInvoice $invoice,
    Fixtures $fixture,
    string $fixtureName
) {
    $data = $fixture->request()->invoice()->files($fixtureName);

    $transport = new TransportDetails;

    $transport->origin()
        ->date(Carbon::now()->setDay(12)->setMonth(12)->setYear(2025))
        ->time(Carbon::now()->setHour(10)->setMinute(5))
        ->address('Rua das Flores, 125')
        ->city('Porto')
        ->postalCode('4410-200')
        ->country('PT');

    $transport->destination()
        ->date(Carbon::now()->setDay(13)->setMonth(12)->setYear(2025))
        ->time(Carbon::now()->setHour(10)->setMinute(5))
        ->address('Rua dos Paninhos, 521')
        ->city('Porto')
        ->postalCode('4410-100')
        ->country('PT');

    $transport->vehicleLicensePlate('00-AB-00');

    $invoice->client(new Client(vat: 'PT123456789', name: 'Client Name'));
    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->transport($transport);

    expect($invoice->getPayload())->toMatchArray($data);
})->with('create-invoice', ['transport']);

it('fails when no client is provided with transport', function (
    CreateInvoice $invoice,
    Fixtures $fixture
) {

    $transport = new TransportDetails;

    $transport->origin()
        ->date(Carbon::now())
        ->address('Rua das Flores, 125')
        ->city('Porto')
        ->postalCode('4410-200')
        ->country('PT');

    $transport->destination()
        ->address('Rua dos Paninhos, 521')
        ->city('Porto')
        ->postalCode('4410-100')
        ->country('PT');

    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->transport($transport);

    $invoice->getPayload();
})->with('create-invoice')
    ->throws(
        Exception::class,
        'Client information is required when transport details are provided.'
    );

it('fails when no load date is provided with transport', function (
    CreateInvoice $invoice,
    Fixtures $fixture
) {
    $transport = new TransportDetails;

    $transport->origin()
        ->address('Rua das Flores, 125')
        ->city('Porto')
        ->postalCode('4410-200')
        ->country('PT');

    $transport->destination()
        ->address('Rua dos Paninhos, 521')
        ->city('Porto')
        ->postalCode('4410-100')
        ->country('PT');
    $invoice->client(new Client(vat: 'PT123456789', name: 'Client Name'));
    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->transport($transport);

    $invoice->getPayload();
})->with('create-invoice')->throws(NeedsDateToSetLoadPointException::class);

it('fails when setting an invalid country', function (
    CreateInvoice $invoice,
    Fixtures $fixture
) {
    $transport = new TransportDetails;

    $transport->origin()
        ->date(Carbon::now())
        ->address('Rua das Flores, 125')
        ->city('Porto')
        ->postalCode('4410-200')
        ->country('XX');

    $transport->destination()
        ->address('Rua dos Paninhos, 521')
        ->city('Porto')
        ->postalCode('4410-100')
        ->country('XX');

    $invoice->item(new Item(reference: 'reference-1'));
    $invoice->transport($transport);

    $invoice->getPayload();
})->with('create-invoice')->throws(InvalidCountryException::class);
