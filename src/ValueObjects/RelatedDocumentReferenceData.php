<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use Spatie\LaravelData\Data;

class RelatedDocumentReferenceData extends Data
{
    public function __construct(
        public string $documentId,
        public int $row,
    ) {}
}
