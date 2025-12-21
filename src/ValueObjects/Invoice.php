<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\ValueObjects;

use CsarCrr\InvoicingIntegration\Contracts\HasData;
use CsarCrr\InvoicingIntegration\ValueObjects\Output;

class Invoice implements HasData
{
    protected int $id;

    protected string $sequence;

    protected Output $output;

    protected ?string $atcudHash = null;

    protected int $total;

    protected int $totalNet;

    public function atcudHash(): ?string
    {
        return $this->atcudHash;
    }

    public function setAtcudHash(?string $atcudHash): self
    {
        $this->atcudHash = $atcudHash;

        return $this;
    }

    public function output(): Output
    {
        return $this->output;
    }

    public function setOutput(Output $output): self
    {
        $this->output = $output;

        return $this;
    }

    public function sequence()
    {
        return $this->sequence;
    }

    public function setSequence(string $sequence): self
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function id()
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function totalNet(): int
    {
        return $this->totalNet;
    }

    public function setTotalNet(int $totalNet): self
    {
        $this->totalNet = $totalNet;

        return $this;
    }
}