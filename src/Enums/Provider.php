<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

enum Provider: string
{
    case CegidVendus = 'CegidVendus';

    public function documents(): string
    {
        return match ($this) {
            self::CegidVendus => 'https://www.vendus.pt/ws/v1.1/documents/',
        };
    }
}

