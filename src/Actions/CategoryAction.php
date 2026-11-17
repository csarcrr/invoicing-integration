<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldCreateCategory;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldFetchCategory;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldFindCategory;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldUpdateCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Category\Create as VendusCreate;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Category\Fetch as VendusFetch;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Category\Find as VendusFind;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Category\Update as VendusUpdate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Category\Create as MoloniCreate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Category\Fetch as MoloniFetch;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Category\Find as MoloniFind;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Category\Update as MoloniUpdate;

final class CategoryAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function find(?CategoryData $category = null): ShouldFindCategory
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new VendusFind,
            Provider::MOLONI => new MoloniFind($category),
        };
    }

    public function fetch(CategoryData $category): ShouldFetchCategory
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new VendusFetch($category),
            Provider::MOLONI => new MoloniFetch($category),
        };
    }

    public function create(CategoryData $category): ShouldCreateCategory
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new VendusCreate($category),
            Provider::MOLONI => new MoloniCreate($category),
        };
    }

    public function update(CategoryData $category): ShouldUpdateCategory
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new VendusUpdate($category),
            Provider::MOLONI => new MoloniUpdate($category),
        };
    }
}
