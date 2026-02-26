<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use Spatie\LaravelData\Data;

class RelatedDocumentReferenceData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    public function __construct(
        public string $documentId,
        public int $row,
    ) {}
}
