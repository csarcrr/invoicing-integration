<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldDeleteItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldFetchItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldFindItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldUpdateItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Vendus\Item\Create as VendusCreate;
use CsarCrr\InvoicingIntegration\Provider\Vendus\Item\Delete as VendusDelete;
use CsarCrr\InvoicingIntegration\Provider\Vendus\Item\Fetch;
use CsarCrr\InvoicingIntegration\Provider\Vendus\Item\Find;
use CsarCrr\InvoicingIntegration\Provider\Vendus\Item\Update as VendusUpdate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item\Create as MoloniCreate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item\Delete as MoloniDelete;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item\Fetch as MoloniFetch;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item\Find as MoloniFind;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item\Update as MoloniUpdate;

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
            Provider::VENDUS => (new VendusCreate($item))->config($this->provider->getConfig()),
            Provider::MOLONI => (new MoloniCreate($item))->config($this->provider->getConfig()),
        };
    }

    public function update(ItemData $item): ShouldUpdateItem
    {
        return match ($this->provider->getProvider()) {
            Provider::VENDUS => (new VendusUpdate($item))->config($this->provider->getConfig()),
            Provider::MOLONI => (new MoloniUpdate($item))->config($this->provider->getConfig()),
        };
    }

    public function delete(ItemData $item): ShouldDeleteItem
    {
        return match ($this->provider->getProvider()) {
            Provider::VENDUS => (new VendusDelete($item))->config($this->provider->getConfig()),
            Provider::MOLONI => (new MoloniDelete($item))->config($this->provider->getConfig()),
        };
    }

    public function fetch(ItemData $item): ShouldFetchItem
    {
        return match ($this->provider->getProvider()) {
            Provider::VENDUS => (new Fetch($item))->config($this->provider->getConfig()),
            Provider::MOLONI => (new MoloniFetch($item))->config($this->provider->getConfig()),
        };
    }

    public function find(?ItemData $item = null): ShouldFindItem
    {
        return match ($this->provider->getProvider()) {
            Provider::VENDUS => (new Find($item))->config($this->provider->getConfig()),
            Provider::MOLONI => (new MoloniFind($item))->config($this->provider->getConfig()),
        };
    }
}
