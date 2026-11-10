<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Company\ShouldFindCompany;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Company\Find;

final class CompanyAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function find(): ShouldFindCompany
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Find,
        };
    }
}
