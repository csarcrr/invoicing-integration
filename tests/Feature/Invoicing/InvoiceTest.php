<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Enums\DocumentPaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\DocumentType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\Invoice\InvoiceItem;
use CsarCrr\InvoicingIntegration\InvoicePayment;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->invoice = Invoice::create();
});

// function mockTurnstileResponse(): void
// {
//     $path = base_path('tests/Fixtures/Providers/CegidVendus/fail.json');

//     $jsonFixture = File::json($path);

//     Http::fake([
//         'https://example.com' => Http::response($jsonFixture),
//     ]);
// }

test('can invoice successfully with minimum data', function (Provider $provider) {
    Http::fake(mockResponse($provider, 'success'));

    $this->invoice->addItem(new InvoiceItem('reference-1'));

    $response = $this->invoice->invoice();

    expect($response)->toBeInstanceOf(InvoiceData::class);
    expect($response->sequence())->toBe('FT 01P2025/1');
})->with([
    Provider::CegidVendus,
]);

test(
    'can invoice and emit a receipt for that invoice',
    function (Provider $provider) {
        Http::fake([
            $provider->documents() => mockResponse($provider, 'success'),
            $provider->documents() => mockResponse($provider, 'success', 200, [
                $provider->field('document_id') => 'RG 01P2025/1',
            ]),
        ]);

        $item = new InvoiceItem('reference-1');
        $item->setPrice(500);

        $this->invoice->setType(DocumentType::Invoice);
        $this->invoice->addItem($item);

        $details = $this->invoice->invoice();

        $receipt = Invoice::create();
        $receipt->setType(DocumentType::Receipt);
        $receipt->addPayment(new InvoicePayment(DocumentPaymentMethod::MONEY, 500));
        $receipt->addRelatedDocument($details->sequence());

        $details = $receipt->invoice();

        expect($details)->toBeInstanceOf(InvoiceData::class);
        expect($details->sequence())->toBe('RG 01P2025/1');
    }
)->with([
    Provider::CegidVendus,
]);

test('handle integration errors', function (Provider $provider) {
    Http::fake([
        $provider->documents() => mockResponse($provider, 'fail', 400),
    ]);

    $this->invoice->addItem(new InvoiceItem('reference-1'));
    $this->invoice->invoice();
})->with([
    Provider::CegidVendus,
])->throws(RequestFailedException::class);
