<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item;

use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;

interface ShouldDeleteItem extends ShouldHavePayload
{
    public function execute(): void;

    public function getUrl(): string;
}
