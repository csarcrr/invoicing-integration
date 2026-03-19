<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use Carbon\Carbon;
use Closure;
use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use Illuminate\Support\Optional;
use League\ISO3166\ISO3166;
use Spatie\LaravelData\Attributes\MergeValidationRules;
use Spatie\LaravelData\Data;

#[MergeValidationRules]
class AddressData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    public function __construct(
        public ?string $address = null,
        public ?string $city = null,
        public ?string $postalCode = null,
        public Optional|string $country = 'PT',
        public ?Carbon $dateTime = null,
    ) {}

    /**
     * @return array<string, array<int, Closure(string, mixed, Closure): void>>
     */
    public static function rules(): array
    {
        return [
            'country' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    try {
                        $data = (new ISO3166)->alpha2(strtolower($value));
                    } catch (\Exception $e) {
                        $fail('Invalid country code');
                    }
                },
            ],
        ];
    }
}
