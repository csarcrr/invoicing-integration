<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums\Tax;

enum DocumentItemTax
{
    case NORMAL;
    case INTERMEDIATE;
    case REDUCED;
    case EXEMPT;
}
