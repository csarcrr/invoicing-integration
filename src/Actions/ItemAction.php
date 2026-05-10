<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldFindItem;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldGetItem;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item\Create as VendusCreate;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item\Find;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item\Get;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item\Create as MoloniCreate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Item\Get as MoloniGet;

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

    public function get(ItemData $item): ShouldGetItem
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Get($item),
            Provider::MOLONI => new MoloniGet($item),
        };
    }

    public function find(?ItemData $item = null): ShouldFindItem
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new Find($item),
            Provider::MOLONI => throw new \Exception('Not currently supported'),
        };
    }
}
