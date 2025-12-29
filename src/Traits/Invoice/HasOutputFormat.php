<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits\Invoice;

use CsarCrr\InvoicingIntegration\Enums\OutputFormat;

trait HasOutputFormat
{
    protected ?OutputFormat $outputFormat = OutputFormat::PDF_BASE64;

    public function outputFormat(OutputFormat $outputFormat): self
    {
        $this->outputFormat = $outputFormat;

        return $this;
    }

    public function getOutputFormat(): OutputFormat
    {
        return $this->outputFormat;
    }
}