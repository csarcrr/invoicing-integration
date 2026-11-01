<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\ItemType;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('sends the provider item type', function (Provider $provider, ItemType $type, string $vendusType, int $moloniType) {
    $payload = Item::create(ItemData::make([
        'category' => CategoryData::make(['id' => 1]),
        'type' => $type,
    ]))->getPayload();

    match ($provider) {
        Provider::CEGID_VENDUS => expect($payload->get('type_id'))->toBe($vendusType),
        Provider::MOLONI => expect($payload->get('type'))->toBe($moloniType),
    };
})->with('providers')->with([
    [ItemType::Product, 'P', 1],
    [ItemType::Service, 'S', 2],
    [ItemType::Other, 'O', 3],
    [ItemType::Tax, 'I', 3],
    [ItemType::SpecialTax, 'E', 3],
]);
