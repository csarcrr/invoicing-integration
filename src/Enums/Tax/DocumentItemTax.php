<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums\Tax;

enum DocumentItemTax
{
    case NORMAL;
    case INTERMEDIATE;
    case REDUCED;
    case EXEMPT;
    case OTHER;

    public function vendus(): string
    {
        return match ($this) {
            DocumentItemTax::NORMAL => 'NOR',
            DocumentItemTax::INTERMEDIATE => 'INT',
            DocumentItemTax::REDUCED => 'RED',
            DocumentItemTax::EXEMPT => 'ISE',
            DocumentItemTax::OTHER => 'OUT',
        };
    }
}
