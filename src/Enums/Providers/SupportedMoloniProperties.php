<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums\Providers;

enum SupportedMoloniProperties: string
{
    case Item = 'item';
    case Client = 'client';
    case Invoice = 'invoice';
    case DueDate = 'due_date';
    case PaymentMethod = 'payment_method';

    /** @return array<string> */
    public function properties(): array
    {
        return match ($this) {
            self::Client => ['name', 'vat', 'address', 'city', 'zip_code', 'email', 'phone', 'notes'],
            self::Invoice => ['document_id', 'number', 'net_value', 'taxes_value'],
            self::DueDate => ['maturity_date_id', 'name', 'days'],
            self::PaymentMethod => ['payment_method_id', 'name', 'is_numerary', 'is_mb', 'is_credit'],
            self::Item => ['product_id', 'category_id', 'type', 'name', 'summary', 'reference', 'ean', 'price', 'has_stock', 'taxes', 'exemption_reason'],
        };
    }
}
