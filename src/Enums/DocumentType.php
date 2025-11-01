<?php

namespace CsarCrr\InvoicingIntegration\Enums;

enum DocumentType: string
{
    case Fatura = 'FT';
    case FaturaRecibo = 'FR';
    case FaturaSimples = 'FS';

    static public function options(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
