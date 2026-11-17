<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Unit;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\UnitData;

interface ShouldCreateUnit extends ShouldExecute, ShouldHavePayload
{
    public function get(): UnitData;

    public function getUrl(): string;
}
