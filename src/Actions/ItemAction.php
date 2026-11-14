<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldDeleteItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldFindItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldGetItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldUpdateItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item\Create as VendusCreate;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item\Delete as VendusDelete;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item\Find;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item\Get;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item\Update as VendusUpdate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item\Create as MoloniCreate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item\Delete as MoloniDelete;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item\Find as MoloniFind;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item\Get as MoloniGet;
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
            Provider::CEGID_VENDUS => (new VendusCreate($item))->config($this->provider->getConfig()),
            Provider::MOLONI => (new MoloniCreate($item))->config($this->provider->getConfig()),
        };
    }

    public function update(ItemData $item): ShouldUpdateItem
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => (new VendusUpdate($item))->config($this->provider->getConfig()),
            Provider::MOLONI => (new MoloniUpdate($item))->config($this->provider->getConfig()),
        };
    }

    public function delete(ItemData $item): ShouldDeleteItem
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => (new VendusDelete($item))->config($this->provider->getConfig()),
            Provider::MOLONI => (new MoloniDelete($item))->config($this->provider->getConfig()),
        };
    }

    public function get(ItemData $item): ShouldGetItem
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => (new Get($item))->config($this->provider->getConfig()),
            Provider::MOLONI => (new MoloniGet($item))->config($this->provider->getConfig()),
        };
    }

    public function find(?ItemData $item = null): ShouldFindItem
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => (new Find($item))->config($this->provider->getConfig()),
            Provider::MOLONI => (new MoloniFind($item))->config($this->provider->getConfig()),
        };
    }
}
