<?php

namespace CsarCrr\InvoicingIntegration\Traits;

trait EnumOptions
{
    public static function options(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
