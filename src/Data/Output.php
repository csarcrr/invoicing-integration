<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Output
{
    protected ?string $path = null;

    public function __construct(
        protected OutputFormat $format,
        protected string $content,
        protected ?string $fileName = null
    ) {
        $this->format = $format;
        $this->setFileName($fileName);
    }

    public function save(?string $path = null): string
    {
        $this->path = $path ?? "invoices/{$this->fileName()}";

        return match ($this->format) {
            OutputFormat::PDF_BASE64 => $this->base64EncodedPdf(),
            OutputFormat::ESCPOS => $this->base64EncodedEscPos(),
        };
    }

    public function get(): string
    {
        return $this->save();
    }

    public function format(): OutputFormat
    {
        return $this->format;
    }

    public function setFormat(OutputFormat $format): void
    {
        $this->format = $format;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function fileName(): ?string
    {
        return $this->fileName;
    }

    protected function setFileName(?string $fileName): self
    {
        $fileName = Str::replace('/', '_', $fileName);

        $this->fileName = Str::of(Str::lower($fileName))->slug('_').'.pdf';

        return $this;
    }

    protected function base64EncodedPdf(): string
    {
        $decoded = base64_decode($this->content);

        Storage::disk('local')->put($this->path, $decoded);

        return $this->path;
    }

    protected function base64EncodedEscPos(): string
    {
        return base64_decode($this->content);
    }
}
