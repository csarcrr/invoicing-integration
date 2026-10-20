<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\PaymentMethod;
use Spatie\LaravelData\Data;

dataset('dataWithAdditional', [
    'invoice' => fn () => InvoiceData::make([]),
    'item' => fn () => ItemData::make(['reference' => 'reference-1']),
    'client' => fn () => ClientData::from(['name' => 'Alberto']),
    'due date' => fn () => DueDateData::make(['id' => 1, 'name' => 'Pronto Pagamento', 'days' => 0]),
    'payment method' => fn () => PaymentMethodData::make(['id' => 1, 'name' => 'Numerário', 'type' => PaymentMethod::MONEY]),
]);

it('leaves unsupported provider fields out of the serialized data', function (Data $data) {
    $data->additional(['rsa_hash' => 'hash', 'lastmodifiedby' => 'Tiago Lopes']);

    expect($data->toArray())->not->toHaveKeys(['rsa_hash', 'lastmodifiedby', 'additional'])
        ->and($data->getAdditionalData())->toBe(['rsa_hash' => 'hash', 'lastmodifiedby' => 'Tiago Lopes']);
})->with('dataWithAdditional');

it('does not let unsupported provider fields overwrite the data properties', function () {
    $invoice = InvoiceData::make(['notes' => 'Package notes']);
    $invoice->additional(['notes' => 'Provider notes']);

    expect($invoice->toArray()['notes'])->toBe('Package notes')
        ->and($invoice->getAdditionalData())->toBe(['notes' => 'Provider notes']);
});

it('leaves unsupported provider fields of nested data out of the serialized data', function () {
    $item = ItemData::make(['reference' => 'reference-1']);
    $item->additional(['warehouse_id' => 512790]);

    $invoice = InvoiceData::from(['items' => collect([$item])]);

    expect($invoice->toArray()['items'][0])->not->toHaveKeys(['warehouse_id', 'additional']);
});
