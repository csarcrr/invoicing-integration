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
    $this->provider = Provider::from(config('invoicing-integration.provider'));
    $this->invoice = Invoice::create();
});

test('can invoice successfully with minimum data', function () {
    Http::fake(mockResponse($this->provider, 'success'));

    $this->invoice->addItem(new InvoiceItem('reference-1'));

    $response = $this->invoice->invoice();

    expect($response)->toBeInstanceOf(InvoiceData::class);
    expect($response->sequence())->toBe('FT 01P2025/1');
});

test(
    'can invoice and emit a receipt for that invoice',
    function () {
        Http::fake([
            $this->provider->documents() => mockResponse($this->provider, 'success'),
            $this->provider->documents() => mockResponse($this->provider, 'success', 200, [
                $this->provider->field('document_id') => 'RG 01P2025/1',
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
);

test('handle integration errors', function () {
    Http::fake([
        $this->provider->documents() => mockResponse($this->provider, 'fail', 400),
    ]);

    $this->invoice->addItem(new InvoiceItem('reference-1'));
    $this->invoice->invoice();
})->throws(RequestFailedException::class);
