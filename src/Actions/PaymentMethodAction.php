<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod\ShouldCreatePaymentMethod;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\PaymentMethod\ShouldFindPaymentMethod;
use CsarCrr\InvoicingIntegration\Data\PaymentMethodData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\PaymentMethod\Create;
use CsarCrr\InvoicingIntegration\Provider\Moloni\PaymentMethod\Find;

/**
 * Orchestrates payment method operations by routing them to the correct provider implementation.
 */
final class PaymentMethodAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function create(PaymentMethodData $paymentMethod): ShouldCreatePaymentMethod
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Create($paymentMethod),
        };
    }

    public function find(): ShouldFindPaymentMethod
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Find,
        };
    }
}
