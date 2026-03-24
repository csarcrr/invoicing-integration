<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

use CsarCrr\InvoicingIntegration\Enums\Providers\SupportedCegidVendusProperties;

enum Provider: string
{
    case CEGID_VENDUS = 'CegidVendus';
    case MOLONI = 'MOLONI';

    /**
     * @return array<string>
     */
    public function supportedProperties(Property $property): array
    {
        return match ($this) {
            self::CEGID_VENDUS => SupportedCegidVendusProperties::from($property->value)->properties(),
            self::MOLONI => [],
        };
    }
}
