<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums\Providers;

enum SupportedMoloniProperties: string
{
    case Item = 'item';

    /** @return array<string> */
    public function properties(): array
    {
        return match ($this) {
            self::Item => [],
        };
    }
}
