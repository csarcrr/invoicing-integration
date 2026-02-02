<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use CsarCrr\InvoicingIntegration\Transformers\Name;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

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
        public Optional|bool $emailNotification = true,
        #[MapName(SnakeCaseMapper::class)]
        public Optional|bool $irsRetention = false,
    ) {}
}
