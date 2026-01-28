<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\CreateInvoice;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Invoice\Create;
use CsarCrr\InvoicingIntegration\Services\ProviderConfigurationService;

final class InvoiceAction
{
    protected string $action;

    public function __construct(
        protected ProviderConfigurationService $providerConfiguration
    ) {}

    public static function create(): CreateInvoice
    {
        $class = app()->make(self::class);

        return match ($class->providerConfiguration->getProvider()) {
            Provider::CEGID_VENDUS => new Create($class->providerConfiguration->getConfig()),
        };
    }
}
