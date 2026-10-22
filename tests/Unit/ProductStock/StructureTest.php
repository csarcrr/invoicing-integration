<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldDeleteProductStock;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldGetProductStock;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldMoveProductStock;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ProductStock\ShouldUpdateProductStock;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ProductStock;

it('is an instance of the correct contract for each operation', function (Provider $provider, Closure $operation, string $contract) {
    match ($provider) {
        Provider::CEGID_VENDUS => expect($operation)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($operation())->toBeInstanceOf($contract),
    };
})->with('providers')->with([
    'move' => [fn () => ProductStock::move(StockMovementData::make([])), ShouldMoveProductStock::class],
    'update' => [fn () => ProductStock::update(StockMovementData::make([])), ShouldUpdateProductStock::class],
    'delete' => [fn () => ProductStock::delete(StockMovementData::make([])), ShouldDeleteProductStock::class],
    'get' => [fn () => ProductStock::get(ItemData::make(['id' => 1542])), ShouldGetProductStock::class],
]);
