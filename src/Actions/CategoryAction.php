<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldCreateCategory;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldFindCategory;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category\ShouldGetCategory;
use CsarCrr\InvoicingIntegration\Data\CategoryData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Category\Create as VendusCreate;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Category\Find as VendusFind;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Category\Get as VendusGet;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Category\Create as MoloniCreate;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Category\Find as MoloniFind;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Category\Get as MoloniGet;

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

    public function get(CategoryData $category): ShouldGetCategory
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new VendusGet($category),
            Provider::MOLONI => new MoloniGet($category),
        };
    }

    public function create(CategoryData $category): ShouldCreateCategory
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new VendusCreate($category),
            Provider::MOLONI => new MoloniCreate($category),
        };
    }
}
