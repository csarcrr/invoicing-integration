<?php

namespace CsarCrr\InvoicingIntegration\Commands;

use Illuminate\Console\Command;

class InvoicingIntegrationCommand extends Command
{
    public $signature = 'invoicing-integration';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
