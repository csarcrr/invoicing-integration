<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Exceptions\InvalidCountryException;
use Illuminate\Support\Optional;
use League\ISO3166\ISO3166;
use Spatie\LaravelData\Attributes\MergeValidationRules;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Closure;

#[MergeValidationRules]
class AddressData extends Data implements DataNeedsValidation
{
    public function __construct (
        public ?string $address = null,
        public ?string $city = null,
        public ?string $postalCode = null,
        public Optional|string $country = 'PT',
        public ?Carbon $date = null,
        public ?Carbon $time = null,
    ) {

    }

    public static function make(array $data): self {
        return AddressData::validateAndCreate($data);
    }

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
                }
            ],
        ];
    }
//    public function country(string $country): self
//    {
//        try {
//            $data = (new ISO3166)->alpha2(strtolower($country));
//        } catch (\Exception $e) {
//            throw new InvalidCountryException;
//        }
//
//        $this->data[$this->type]['country'] = $data['alpha2'];
//
//        return $this;
//    }
}
