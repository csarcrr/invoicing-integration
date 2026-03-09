<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums\Providers;

enum SupportedCegidVendusProperties: string
{
    case Item = 'item';
    case Client = 'client';
    case Invoice = 'invoice';

    /**
     * @return array<string>
     */
    public function properties(): array
    {
        return match ($this) {
            self::Item => ['title', 'reference', 'gross_price', 'description', 'type_id', 'tax_id', 'tax_exemption', 'tax_exemption_law', 'barcode', 'category_id', 'stock_control', 'status', 'unit_id'],
            self::Client => ['id', 'name', 'email', 'postalcode', 'country', 'city', 'address', 'phone', 'notes', 'default_pay_due', 'fiscal_id', 'send_email', 'irs_retention', 'date'],
            self::Invoice => ['id', 'type', 'number', 'amount_gross', 'amount_net', 'atcud', 'output']
        };
    }
}
