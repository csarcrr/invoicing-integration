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
            self::Item => ['company_id', 'category_id', 'type', 'name', 'reference', 'price', 'unit_id', 'has_stock', 'stock', 'taxes'],
        };
    }
}
