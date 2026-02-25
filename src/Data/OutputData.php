<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Data;

use CsarCrr\InvoicingIntegration\Contracts\DataNeedsValidation;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Traits\HasMakeValidation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\MergeValidationRules;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

#[MergeValidationRules]
class OutputData extends Data implements DataNeedsValidation
{
    use HasMakeValidation;

    protected string $path;

    public function __construct(
        public OutputFormat $format = OutputFormat::PDF_BASE64,
        public Optional|string $content = '',
        public Optional|string $fileName = '',
    ) {}

    public function save(?string $path = null): string
    {
        if(empty($this->fileName) || $this->fileName instanceof Optional) {
            $this->fileName = Str::random(4).time();
        }

        $this->path = $this->sanitizePath($path ?? "invoices/{$this->fileName}");

        return match ($this->format) {
            OutputFormat::PDF_BASE64 => $this->base64EncodedPdf(),
            OutputFormat::ESCPOS => $this->base64EncodedEscPos(),
        };
    }

    protected function sanitizePath(string $path): string
    {
        return Str::of($path)
            ->ltrim('/\\')
            ->squish()
            ->replaceMatches('/[\x00-\x1F]|\\\\x[0-1][0-9A-Fa-f]/', '')
            ->replace(['../', '..\\', '..', '#', '@', '!'], '')
            ->replace([' ', '-'], '_')
            ->lower()
            ->snake()
            ->ascii()
            ->toString();
    }

    protected function base64EncodedPdf(): string
    {
        if(!is_string($this->content)) {
            return  '';
        }

        $decoded = base64_decode($this->content);

        $this->ensurePdfSuffix();

        Storage::disk('local')->put($this->path, $decoded);

        return $this->path;
    }

    protected function base64EncodedEscPos(): string
    {
        if($this->content instanceof Optional) {
            return '';
        }

        return base64_decode($this->content);
    }

    private function ensurePdfSuffix(): void
    {
        if (! Str::endsWith($this->path, '.pdf')) {
            $this->path .= '.pdf';
        }
    }
}
