<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums\Tax;

enum ItemTax: string
{
    case NORMAL = 'NOR';
    case INTERMEDIATE = 'INT';
    case REDUCED = 'RED';
    case EXEMPT = 'ISE';
    case OTHER = 'OUT';

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
