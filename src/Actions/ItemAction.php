<?php

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item\Create;

/**
 * Orchestrates item operations by routing them to the correct provider implementation.
 */
final class ItemAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    /**
     * Returns a provider-specific implementation to create an item.
     */
    public function create(ItemData $item): ShouldCreateItem
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => (new Create($item))->config($this->provider->getConfig()),
        };
    }
}
