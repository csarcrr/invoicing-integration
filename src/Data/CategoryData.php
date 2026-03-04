<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use Spatie\LaravelData\Data;

class CategoryData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
    ) {}
}
