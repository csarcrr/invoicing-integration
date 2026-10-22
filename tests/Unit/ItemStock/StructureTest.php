<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldDeleteItemStock;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldMoveItemStock;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;

it('is an instance of the correct contract for each operation', function (Provider $provider, Closure $operation, string $contract) {
    match ($provider) {
        Provider::CEGID_VENDUS => expect($operation)->toThrow(OperationNotSupportedException::class),
        Provider::MOLONI => expect($operation())->toBeInstanceOf($contract),
    };
})->with('providers')->with([
    'move' => [fn () => ItemStock::move(StockMovementData::make([])), ShouldMoveItemStock::class],
    'delete' => [fn () => ItemStock::delete(StockMovementData::make([])), ShouldDeleteItemStock::class],
]);
