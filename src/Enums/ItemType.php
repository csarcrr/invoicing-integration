<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

use CsarCrr\InvoicingIntegration\Traits\EnumOptions;

enum ItemType: string
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
            ItemType::Product => 'P',
            ItemType::Service => 'S',
            ItemType::Other => 'O',
            ItemType::Tax => 'I',
            ItemType::SpecialTax => 'E',
        };
    }
}
