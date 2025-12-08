<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

class InvoiceData
{
    protected int $id;
    protected string $sequence;
    protected Output $output;

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
}
