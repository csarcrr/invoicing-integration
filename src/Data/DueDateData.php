<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use Spatie\LaravelData\Data;

class DueDateData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    public function __construct(
        public string $name,
        public int $days,
        public ?int $id = null,
    ) {}
}
