<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

use CsarCrr\InvoicingIntegration\Traits\EnumOptions;

enum InvoiceItemType: string
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
            InvoiceItemType::Product => 'P',
            InvoiceItemType::Service => 'S',
            InvoiceItemType::Other => 'O',
            InvoiceItemType::Tax => 'I',
            InvoiceItemType::SpecialTax => 'E',
        };
    }
}
