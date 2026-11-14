<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoicePdf;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\OutputData;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function base64_encode;
use function is_int;
use function parse_str;
use function parse_url;
use function str_starts_with;
use function throw_if;

class Pdf implements ShouldGetInvoicePdf
{
    protected OutputData $output;

    public function __construct(protected InvoiceData $invoice) {}

    public function execute(): self
    {
        throw_if(! is_int($this->invoice->id), InvalidArgumentException::class, 'Invoice ID is required.');

        $response = Http::provider()->post('documents/getPDFLink', ['document_id' => $this->invoice->id]);

        Http::handleUnwantedFailures($response);

        $url = (string) $response->json('url', '');

        throw_if($url === '', RequestFailedException::class, 'Invoice PDF link not available.');

        $this->output = OutputData::make([
            'format' => OutputFormat::PDF_BASE64->value,
            'content' => $this->downloadPdf($url),
            'url' => $url,
        ]);

        return $this;
    }

    public function getOutput(): OutputData
    {
        return $this->output;
    }

    private function downloadPdf(string $url): ?string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $download = Http::get('https://www.moloni.pt/downloads/index.php', [
            'action' => 'getDownload',
            'h' => $query['h'] ?? '',
            'd' => $this->invoice->id,
        ]);

        if (! $download->successful() || ! str_starts_with((string) $download->header('Content-Type'), 'application/pdf')) {
            return null;
        }

        return base64_encode($download->body());
    }
}
