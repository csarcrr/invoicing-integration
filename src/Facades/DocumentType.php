<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\Actions\DocumentTypeAction;
use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DocumentType\ShouldFindDocumentType;
use CsarCrr\InvoicingIntegration\Data\LanguageData;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ShouldFindDocumentType find(?LanguageData $language = null)
 *
 * @see \CsarCrr\InvoicingIntegration\Actions\DocumentTypeAction
 */
class DocumentType extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return DocumentTypeAction::class;
    }
}
