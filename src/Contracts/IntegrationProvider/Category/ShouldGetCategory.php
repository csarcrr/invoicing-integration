<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Data\CategoryData;

interface ShouldGetCategory extends ShouldExecute
{
    public function execute(): self;

    public function getCategory(): CategoryData;
}
