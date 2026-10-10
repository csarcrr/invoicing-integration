<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Actions;

use CsarCrr\InvoicingIntegration\Configuration\ProviderConfigurationService;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate\ShouldCreateDueDate;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DueDate\ShouldFindDueDate;
use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\OperationNotSupportedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\DueDate\Create;
use CsarCrr\InvoicingIntegration\Provider\Moloni\DueDate\Find;

/**
 * Orchestrates due date operations by routing them to the correct provider implementation.
 */
final class DueDateAction
{
    public function __construct(
        protected ProviderConfigurationService $provider
    ) {}

    public function create(DueDateData $dueDate): ShouldCreateDueDate
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Create($dueDate),
        };
    }

    public function find(): ShouldFindDueDate
    {
        return match ($this->provider->getProvider()) {
            Provider::CEGID_VENDUS => throw new OperationNotSupportedException,
            Provider::MOLONI => new Find,
        };
    }
}
