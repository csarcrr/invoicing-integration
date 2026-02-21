<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use Carbon\Carbon;
use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Enums\InvoiceType;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use CsarCrr\InvoicingIntegration\ValueObjects\Output;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use CsarCrr\InvoicingIntegration\Data\PaymentData;
use CsarCrr\InvoicingIntegration\Data\ItemData;

/**
 *
 */
class InvoiceData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    public function __construct(
        public Optional|int $id,
        public Optional|string $sequence,
        public Optional|int $total,
        public Optional|int $totalNet,

        /** @var Collection<int, ItemData> */
        public Optional|Collection $items,
        /** @var Collection<int, PaymentData> */
        public Optional|Collection $payments,

        public Optional|ClientData $client,
        public Optional|TransportData $transport,

        public ?string $atcudHash = null,
        public ?Output $output = null,
        public InvoiceType $type = InvoiceType::Invoice,
        public Optional|null|Carbon $dueDate = null,
        public Optional|null|string $notes = null
    ) {}
}
