<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ItemData;

interface ShouldGetItem extends ShouldExecute, ShouldHavePayload
{
    public function execute(): self;

    public function getItem(): ItemData;

    public function getUrl(): string;
}
