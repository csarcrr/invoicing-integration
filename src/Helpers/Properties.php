<?php

namespace CsarCrr\InvoicingIntegration\Helpers;

use Spatie\LaravelData\Optional;

class Properties
{
    public static function isValid(mixed $value): bool
    {
        return ! self::isNotValid($value);
    }

    public static function isNotValid(mixed $value): bool
    {
        return $value instanceof Optional || empty($value);
    }
}
