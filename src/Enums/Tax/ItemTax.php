<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums\Tax;

enum ItemTax
{
    case NORMAL;
    case INTERMEDIATE;
    case REDUCED;
    case EXEMPT;
    case OTHER;

    public function vendus(): string
    {
        return match ($this) {
            ItemTax::NORMAL => 'NOR',
            ItemTax::INTERMEDIATE => 'INT',
            ItemTax::REDUCED => 'RED',
            ItemTax::EXEMPT => 'ISE',
            ItemTax::OTHER => 'OUT',
        };
    }
}
