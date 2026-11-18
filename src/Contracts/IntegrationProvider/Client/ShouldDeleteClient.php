<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;

interface ShouldDeleteClient extends ShouldHavePayload
{
    public function execute(): void;

    public function getUrl(): string;
}
