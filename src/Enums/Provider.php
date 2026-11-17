<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

use CsarCrr\InvoicingIntegration\Enums\Providers\SupportedVendusProperties;
use CsarCrr\InvoicingIntegration\Enums\Providers\SupportedMoloniProperties;

enum Provider: string
{
    case VENDUS = 'Vendus';
    case MOLONI = 'Moloni';

    /**
     * @return array<string>
     */
    public function supportedProperties(Property $property): array
    {
        return match ($this) {
            self::VENDUS => SupportedVendusProperties::from($property->value)->properties(),
            self::MOLONI => SupportedMoloniProperties::from($property->value)->properties(),
        };
    }
}
