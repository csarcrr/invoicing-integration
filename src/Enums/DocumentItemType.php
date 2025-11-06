<?php

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
}
