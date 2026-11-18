<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\CategoryData;

interface ShouldUpdateCategory extends ShouldExecute, ShouldHavePayload
{
    public function get(): CategoryData;

    public function getUrl(): string;
}
