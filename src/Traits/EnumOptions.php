<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

trait EnumOptions
{
    /**
     * @return array<int, string|int>
     */
    public static function options(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
