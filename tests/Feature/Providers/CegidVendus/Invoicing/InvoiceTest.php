<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\ProviderConfig;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Invoice as ValueObjectsInvoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Item;
use CsarCrr\InvoicingIntegration\ValueObjects\Payment;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->provider = ProviderConfig::from(config('invoicing-integration.provider'));
    $this->invoice = Invoice::create();
});

test('can invoice successfully with minimum data', function () {
    Http::fake(mockResponse($this->provider, 'success'));

    $this->invoice->addItem(new Item('reference-1'));

    $response = $this->invoice->execute();

    expect($response)->toBeInstanceOf(ValueObjectsInvoice::class);
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

        $item = new Item('reference-1');
        $item->setPrice(500);

        $this->invoice->setType(InvoiceType::Invoice);
        $this->invoice->addItem($item);

        $details = $this->invoice->execute();

        $receipt = Invoice::create();
        $receipt->setType(InvoiceType::Receipt);
        $receipt->addPayment(new Payment(PaymentMethod::MONEY, 500));
        $receipt->addRelatedDocument($details->sequence());

        $details = $receipt->execute();

        expect($details)->toBeInstanceOf(ValueObjectsInvoice::class);
        expect($details->sequence())->toBe('RG 01P2025/1');
    }
);

test('handle integration errors', function () {
    Http::fake([
        $this->provider->documents() => mockResponse($this->provider, 'fail', 400),
    ]);

    $this->invoice->addItem(new Item('reference-1'));
    $this->invoice->execute();
})->throws(RequestFailedException::class);
