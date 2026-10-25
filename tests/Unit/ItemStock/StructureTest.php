<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\ItemStock\ShouldMoveItemStock;
use CsarCrr\InvoicingIntegration\Data\StockMovementData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\ItemStock;

it('is an instance of the move contract', function (Provider $provider) {
    expect(ItemStock::move(StockMovementData::make([])))->toBeInstanceOf(ShouldMoveItemStock::class);
})->with('providers');
