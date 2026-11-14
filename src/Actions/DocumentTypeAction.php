<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DocumentType\ShouldFindDocumentType;
use CsarCrr\InvoicingIntegration\Data\LanguageData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\DocumentType\Find as VendusFind;
use CsarCrr\InvoicingIntegration\Provider\Moloni\DocumentType\Find as MoloniFind;

final class DocumentTypeAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function find(?LanguageData $language = null): ShouldFindDocumentType
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => new VendusFind,
            Provider::MOLONI => new MoloniFind($language),
        };
    }
}
