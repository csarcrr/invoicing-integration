<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

use CsarCrr\InvoicingIntegration\Traits\EnumOptions;

enum DueDateTerm: int
{
    use EnumOptions;

    case Days0 = 0;
    case Days10 = 10;
    case Days15 = 15;
    case Days30 = 30;
    case Days60 = 60;
    case Days90 = 90;
    case Days120 = 120;
}
