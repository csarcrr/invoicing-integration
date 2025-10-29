<?php

namespace CsarCrr\InvoicingIntegration\Enums;

enum DocumentType: string
{
    case Fatura = 'FT';
    case FaturaRecibo = 'FR';
    case FaturaSimples = 'FS';
}
