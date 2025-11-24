<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

use CsarCrr\InvoicingIntegration\Traits\EnumOptions;

enum DocumentItemType: string
{
    use EnumOptions;

    case Product = 'product';
    case Service = 'service';
    case Other = 'other';
    case Tax = 'tax_vat';
    case SpecialTax = 'special_tax';

    public function vendus(): string
    {
        return match ($this) {
            DocumentItemType::Product => 'P',
            DocumentItemType::Service => 'S',
            DocumentItemType::Other => 'O',
            DocumentItemType::Tax => 'I',
            DocumentItemType::SpecialTax => 'E',
        };
    }
}
