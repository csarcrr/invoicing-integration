<?php

use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\NeedsDateToSetLoadPointException;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceTransportDetails;

it('formats transport load point data correctly', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    $transport = new InvoiceTransportDetails;
    $transport->origin()->date(now());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('Countryland');

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->transportDetails($transport)
        ->type(DocumentType::Invoice);

    $resolve->create();

    expect($resolve->payload()->get('movement_of_goods')['loadpoint'])
        ->toEqual([
            'date' => $transport->origin()->date(),
            'time' => $transport->origin()->time(),
            'address' => $transport->origin()->address(),
            'postalcode' => $transport->origin()->postalCode(),
            'city' => $transport->origin()->city(),
            'country' => $transport->origin()->country(),
        ]);
});
it('formats transport land point data correctly', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    $transport = new InvoiceTransportDetails;

    $transport = new InvoiceTransportDetails;
    $transport->origin()->date(now());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('Countryland');

    $transport->destination()->date(now()->addDay());
    $transport->destination()->time(now()->addDay());
    $transport->destination()->address('123 Main St');
    $transport->destination()->city('Cityville');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('Countryland');

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->transportDetails($transport)
        ->type(DocumentType::Invoice);

    $resolve->create();

    expect($resolve->payload()->get('movement_of_goods')['landpoint'])
        ->toEqual([
            'date' => $transport->destination()->date(),
            'time' => $transport->destination()->time(),
            'address' => $transport->destination()->address(),
            'postalcode' => $transport->destination()->postalCode(),
            'city' => $transport->destination()->city(),
            'country' => $transport->destination()->country(),
        ]);
});

it('formats transport vehicle license plate correctly', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    $transport = new InvoiceTransportDetails;

    $transport = new InvoiceTransportDetails;

    $transport->vehicleLicensePlate('ABC-1234');

    $transport->origin()->date(now());
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('Countryland');

    $transport->destination()->date(now()->addDay());
    $transport->destination()->time(now()->addDay());
    $transport->destination()->address('123 Main St');
    $transport->destination()->city('Cityville');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('Countryland');

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->transportDetails($transport)
        ->type(DocumentType::Invoice);

    $resolve->create();

    expect($resolve->payload()->get('movement_of_goods')['vehicle_id'])
        ->toEqual('ABC-1234');
});

it('fails when no date is set for load point', function () {
    $item = new InvoiceItem(reference: 'reference-1');

    $transport = new InvoiceTransportDetails;

    $transport = new InvoiceTransportDetails;
    $transport->origin()->time(now());
    $transport->origin()->address('123 Main St');
    $transport->origin()->city('Cityville');
    $transport->origin()->postalCode('12345');
    $transport->origin()->country('Countryland');

    $transport->destination()->date(now()->addDay());
    $transport->destination()->time(now()->addDay());
    $transport->destination()->address('123 Main St');
    $transport->destination()->city('Cityville');
    $transport->destination()->postalCode('12345');
    $transport->destination()->country('Countryland');

    $resolve = app(config('invoicing-integration.provider'))
        ->items(collect([$item]))
        ->transportDetails($transport)
        ->type(DocumentType::Invoice);

    $resolve->create();
})->throws(NeedsDateToSetLoadPointException::class);
