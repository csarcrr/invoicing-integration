<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use Illuminate\Support\Collection;

interface FindClient
{
    public function execute(): self;

    public function getList(): Collection;

    public function getPayload(): Collection;
}
