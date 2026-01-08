<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

class RelatedDocumentReference
{
    public function __construct(
        public readonly string $documentId,
        public readonly int $row
    ) {}

    public function getDocumentId(): string
    {
        return $this->documentId;
    }

    public function getRow(): int
    {
        return $this->row;
    }
}
