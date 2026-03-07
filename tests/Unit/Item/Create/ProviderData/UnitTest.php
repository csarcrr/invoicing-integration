<?php

use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Unit;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\CouldNotGetUnitIdException;
use CsarCrr\InvoicingIntegration\Facades\Item;

it('fails when unit is not found', function (Provider $provider) {
    $this->markTestSkippedWhen($provider !== Provider::CEGID_VENDUS, 'Only applicable to CEGID VENDUS');

    config()->set('invoicing-integration.providers.'.Provider::CEGID_VENDUS->value.'.units', []);

    Item::create(ItemData::make([
        'unit' => Unit::KG,
    ]))->getPayload();
})->with('providers')->throws(CouldNotGetUnitIdException::class);
