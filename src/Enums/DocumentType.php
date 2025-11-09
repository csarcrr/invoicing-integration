<?php

namespace CsarCrr\InvoicingIntegration\Enums;

use CsarCrr\InvoicingIntegration\Traits\EnumOptions;

enum DocumentType: string
{
    use EnumOptions;

    case Invoice = 'FT';
    case InvoiceReceipt = 'FR';
    case InvoiceSimple = 'FS';
    case Receipt = 'RG';
}
