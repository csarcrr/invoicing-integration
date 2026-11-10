<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax\ShouldCreateTax;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Tax\ShouldFindTax;
use CsarCrr\InvoicingIntegration\Data\TaxData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Tax\Create;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Tax\Find;

final class TaxAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function create(TaxData $tax): ShouldCreateTax
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Create($tax),
        };
    }

    public function find(): ShouldFindTax
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Find,
        };
    }
}
