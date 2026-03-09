<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client;

use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePagination;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

interface ShouldFindClient extends ShouldHavePagination, ShouldHavePayload
{
    public function execute(): self;

    /**
     * @return Collection<int, mixed>
     */
    public function getList(): Collection;

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection;

    /**
     * @return InvoiceData
     */
    public function getClient(): Data;
}
