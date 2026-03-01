<?php

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldCreateInvoice;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item\Create;

final class ItemAction
{
    public function create (ItemData $item) : ShouldCreateInvoice {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Create($item),
        };
    }
}
