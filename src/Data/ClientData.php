<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use CsarCrr\InvoicingIntegration\Transformers\Name;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use const DATE_ATOM;

class ClientData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    public function __construct(
        #[WithTransformer(Name::class)]
        public Optional|null|string $name,
        #[Rule('nullable|email')]
        public Optional|string $email,
        public Optional|string $country,
        public Optional|string $city,
        public Optional|string $address,
        #[MapName(SnakeCaseMapper::class)]
        public Optional|string $postalCode,
        public Optional|string $phone,
        public Optional|string $notes,

        public Optional|int $defaultPayDue,

        public Optional|string|int $vat,
        public Optional|int $id,
        #[MapName(SnakeCaseMapper::class)]
        public Optional|string $externalReference,
        public Optional|string $status,

        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d')]
        public Optional|Carbon $date,

        #[MapName(SnakeCaseMapper::class)]
        public Optional|bool $emailNotification = true,
        #[MapName(SnakeCaseMapper::class)]
        public Optional|bool $irsRetention = false,
    ) {}
}
