<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums\Providers;

enum SupportedMoloniProperties: string
{
    case Item = 'item';
    case Client = 'client';

    /** @return array<string> */
    public function properties(): array
    {
        return match ($this) {
            self::Client => ['name', 'vat', 'address', 'city', 'zip_code', 'email', 'phone', 'notes'],
            self::Item => ['product_id', 'category_id', 'type', 'name', 'summary', 'reference', 'ean', 'price', 'has_stock', 'taxes', 'exemption_reason'],
        };
    }
}
