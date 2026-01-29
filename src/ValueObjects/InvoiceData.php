<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Exceptions\Invoices\InvoiceWithoutOutputException;
use Spatie\LaravelData\Data;

class InvoiceData extends Data
{
    public function __construct(
        public int $id,
        public string $sequence,
        public int $total,
        public int $totalNet,
        public ?string $atcudHash = null,
        public ?Output $output = null,
    ) {}

    public function getAtcudHash(): ?string
    {
        return $this->atcudHash;
    }

    /**
     * @throws InvoiceWithoutOutputException
     */
    public function getOutput(): Output
    {
        throw_if(is_null($this->output), InvoiceWithoutOutputException::class);

        return $this->output;
    }

    public function getSequence(): string
    {
        return $this->sequence;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getTotalNet(): int
    {
        return $this->totalNet;
    }
}
