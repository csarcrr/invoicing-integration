<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse\ShouldCreateWarehouse;
use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\Warehouse;

it('is an instance of the correct when creating a warehouse', function (Provider $provider) {
    $create = fn () => Warehouse::create(WarehouseData::make(['name' => 'Loja']));

    match ($provider) {
        Provider::CEGID_VENDUS => expect($create)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($create())->toBeInstanceOf(ShouldCreateWarehouse::class),
    };
})->with('providers');
