<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums\Providers;

enum SupportedMoloniProperties: string
{
    case Item = 'item';
    case Client = 'client';
    case Invoice = 'invoice';

    /** @return array<string> */
    public function properties(): array
    {
        return match ($this) {
            self::Client => ['name', 'vat', 'address', 'city', 'zip_code', 'email', 'phone', 'notes'],
            self::Invoice => ['document_id', 'number', 'net_value', 'taxes_value'],
            self::Item => ['product_id', 'category_id', 'type', 'name', 'summary', 'reference', 'ean', 'price', 'has_stock', 'taxes', 'exemption_reason'],
        };
    }
}
