<?php

namespace CsarCrr\InvoicingIntegration\Enums;

use CsarCrr\InvoicingIntegration\Traits\EnumOptions;

enum DocumentType: string
{
    use EnumOptions;

    case Fatura = 'FT';
    case FaturaRecibo = 'FR';
    case FaturaSimples = 'FS';
}
