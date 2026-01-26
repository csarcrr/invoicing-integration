<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Contracts\HasData;
use CsarCrr\InvoicingIntegration\Exceptions\Invoices\InvoiceWithoutOutputException;

class Invoice implements HasData
{
    protected int $id;

    protected string $sequence;

    protected ?Output $output = null;

    protected ?string $atcudHash = null;

    protected int $total;

    protected int $totalNet;

    public function getAtcudHash(): ?string
    {
        return $this->atcudHash;
    }

    public function atcudHash(?string $atcudHash): self
    {
        $this->atcudHash = $atcudHash;

        return $this;
    }

    /**
     * @throws InvoiceWithoutOutputException
     */
    public function getOutput(): Output
    {
        throw_if(is_null($this->output), InvoiceWithoutOutputException::class);

        return $this->output;
    }

    public function output(Output $output): self
    {
        $this->output = $output;

        return $this;
    }

    public function getSequence(): string
    {
        return $this->sequence;
    }

    public function sequence(string $sequence): self
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function id(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function total(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getTotalNet(): int
    {
        return $this->totalNet;
    }

    public function totalNet(int $totalNet): self
    {
        $this->totalNet = $totalNet;

        return $this;
    }
}
