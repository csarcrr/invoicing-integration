<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

trait HasRelatedDocument
{
    protected int|string|null $relatedDocument = null;

    protected ?int $relatedDocumentRow = null;

    public function relatedDocument(int|string $relatedDocument, ?int $row = null): self
    {
        $this->relatedDocument = $relatedDocument;
        $this->relatedDocumentRow = $row;

        return $this;
    }

    public function getRelatedDocument(): int|string|null
    {
        return $this->relatedDocument;
    }

    public function getRelatedDocumentRow(): ?int
    {
        return $this->relatedDocumentRow;
    }
}
