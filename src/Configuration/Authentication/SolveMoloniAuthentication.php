<?php

namespace CsarCrr\InvoicingIntegration\Configuration\Authentication;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use Illuminate\Support\Collection;

class SolveMoloniAuthentication implements ShouldExecute, ShouldHavePayload
{
    public function __construct (protected array $config) {
    }

    public function execute(): ShouldExecute
    {
        return $this;
    }

    public function getPayload(): Collection
    {
        return collect([]);
    }
}
