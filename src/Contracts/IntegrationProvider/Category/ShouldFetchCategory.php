<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Data\CategoryData;

interface ShouldFetchCategory extends ShouldExecute
{
    public function get(): CategoryData;
}
