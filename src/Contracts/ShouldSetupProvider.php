<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts;

use Illuminate\Console\Command;

interface ShouldSetupProvider
{
    /**
     * @return array<string, string>
     */
    public function run(Command $command): array;
}
