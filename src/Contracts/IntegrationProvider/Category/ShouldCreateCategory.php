<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Category;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\CategoryData;

interface ShouldCreateCategory extends ShouldExecute, ShouldHavePayload
{
    public function execute(): self;

    public function getCategory(): CategoryData;

    public function getUrl(): string;
}
