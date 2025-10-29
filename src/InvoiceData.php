<?php

namespace CsarCrr\InvoicingIntegration;

class InvoiceData
{
    protected string $sequenceNumber;

    public function sequenceNumber(): string
    {
        return $this->sequenceNumber;
    }

    public function setSequenceNumber(string $sequenceNumber): void
    {
        $this->sequenceNumber = $sequenceNumber;
    }
}
