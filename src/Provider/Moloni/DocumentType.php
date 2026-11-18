<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni;

use CsarCrr\InvoicingIntegration\Data\DocumentTypeData;
use CsarCrr\InvoicingIntegration\Provider\Base;

use function collect;

/** @extends Base<DocumentTypeData> */
abstract class DocumentType extends Base
{
    /**
     * @param  array<string, mixed>  $type
     */
    protected function toDocumentTypeData(array $type): DocumentTypeData
    {
        return DocumentTypeData::make([
            'code' => $type['saft_code'],
            'name' => $type['name'],
        ])->additional(collect($type)->except(['saft_code', 'name'])->toArray());
    }
}
