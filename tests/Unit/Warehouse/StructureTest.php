<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Warehouse\ShouldCreateWarehouse;
use CsarCrr\InvoicingIntegration\Data\WarehouseData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Warehouse;

it('is an instance of the correct when creating a warehouse', function (Provider $provider) {
    expect(Warehouse::create(WarehouseData::make(['name' => 'Loja'])))->toBeInstanceOf(ShouldCreateWarehouse::class);
})->with('providers');
