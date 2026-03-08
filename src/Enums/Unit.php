<?php

namespace CsarCrr\InvoicingIntegration\Enums;

use CsarCrr\InvoicingIntegration\Contracts\ShouldBeUnit;

enum Unit: string implements ShouldBeUnit
{
    case KG = 'kg';
    case UNIT = 'unit';

    public function getUnitKey(): string
    {
        return $this->value;
    }
}
