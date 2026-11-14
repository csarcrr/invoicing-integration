<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DocumentType;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\DocumentTypeData;
use Illuminate\Support\Collection;

interface ShouldFindDocumentType extends ShouldExecute, ShouldHavePayload
{
    public function execute(): self;

    /**
     * @return Collection<int, DocumentTypeData>
     */
    public function getList(): Collection;

    public function getUrl(): string;
}
