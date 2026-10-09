<?php

declare(strict_types=1);

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Enums\DueDateTerm;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Invoice;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    Carbon::setTestNow('2025-06-15');
});

it('transforms to provider payload with due date', function (Provider $provider, string $fixtureName) {
    if ($provider === Provider::MOLONI) {
        Http::fake(mockResponse(fixtures()->response()->dueDate()->files('response_multiple')));
    }

    $data = fixtures()->request()->invoice()->files($fixtureName);

    $invoice = Invoice::create(
        InvoiceData::make([
            'items' => [ItemData::from(['reference' => 'reference-1'])],
            'payments' => [
                PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]),
            ],
            'dueDate' => DueDateTerm::Days30,
        ])
    );

    expect($invoice->getPayload())->toMatchArray($data);
})->with('providers', ['due_date']);

it('resolves the due date term for the provider', function (Provider $provider, DueDateTerm $term, int $maturityDateId, string $date) {
    if ($provider === Provider::MOLONI) {
        Http::fake(mockResponse(fixtures()->response()->dueDate()->files('response_multiple')));
    }

    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'dueDate' => $term,
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('due_date'))->toBe($date),
        Provider::MOLONI => expect($payload->get('expiration_date'))->toBe($date)
            ->and($payload->get('maturity_date_id'))->toBe($maturityDateId),
    };
})->with('providers')->with([
    'exact match' => [DueDateTerm::Days30, 4322, '2025-07-15'],
    'closest above' => [DueDateTerm::Days60, 4322, '2025-08-14'],
    'closest below' => [DueDateTerm::Days10, 4321, '2025-06-25'],
    'tie picks the shorter term' => [DueDateTerm::Days15, 4321, '2025-06-30'],
]);

it('fetches the provider due dates in a single page', function (Provider $provider) {
    if ($provider === Provider::MOLONI) {
        Http::fake(mockResponse(fixtures()->response()->dueDate()->files('response_multiple')));
    }

    Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'dueDate' => DueDateTerm::Days30,
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => Http::assertNothingSent(),
        Provider::MOLONI => Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'maturityDates/getAll')
            && Str::contains($request->body(), 'qty=50')
            && Str::contains($request->body(), 'offset=0')),
    };
})->with('providers');

it('does not send a provider due date id when the provider has none', function (Provider $provider) {
    if ($provider === Provider::MOLONI) {
        Http::fake(mockResponse([]));
    }

    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'dueDate' => DueDateTerm::Days30,
    ]))->getPayload();

    expect($payload)->not->toHaveKey('maturity_date_id');
})->with('providers');

it('does not fetch the provider due dates when no due date is set', function (Provider $provider) {
    $payload = Invoice::create(InvoiceData::make([
        'items' => [ItemData::from(['reference' => 'reference-1'])],
    ]))->getPayload();

    expect($payload)->not->toHaveKey('maturity_date_id');

    Http::assertNotSent(fn (Request $request) => Str::contains($request->url(), 'maturityDates/getAll'));
})->with('providers');

it('fails setting a due date in a type different than FT', function (Provider $provider) {
    $invoice = Invoice::create(InvoiceData::make([
        'type' => InvoiceType::InvoiceSimple,
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'payments' => [
            PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]),
        ],
        'dueDate' => DueDateTerm::Days30,
    ]));

    $invoice->getPayload();
})->with('providers')
    ->throws(Exception::class, 'Due date can only be set for FT document types.');

it('only sends an expiration date on document types that accept it', function (Provider $provider) {
    $invoice = Invoice::create(InvoiceData::make([
        'type' => InvoiceType::Receipt,
        'items' => [ItemData::from(['reference' => 'reference-1'])],
        'payments' => [
            PaymentData::from(['amount' => 500, 'method' => PaymentMethod::CREDIT_CARD]),
        ],
    ]));

    $payload = $invoice->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('type'))->toBe(InvoiceType::Receipt->value),
        Provider::MOLONI => expect($payload)->not->toHaveKey('expiration_date'),
    };
})->with('providers');
