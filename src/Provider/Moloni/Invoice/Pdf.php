<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Invoice\ShouldGetInvoicePdf;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Data\OutputData;
use CsarCrr\InvoicingIntegration\Enums\OutputFormat;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function base64_encode;
use function collect;
use function is_int;
use function parse_str;
use function parse_url;
use function str_starts_with;
use function throw_if;

class Pdf extends Invoice implements ShouldGetInvoicePdf
{
    protected OutputData $output;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(InvoiceData $invoice)
    {
        $this->data = $invoice;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Invoice);

        $this->buildUrl();
        $this->buildPayload();
    }

    public function execute(): self
    {
        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

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

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        return $this->payload;
    }

    protected function buildUrl(): void
    {
        $this->url = 'documents/getPDFLink';
    }

    protected function buildPayload(): void
    {
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Invoice ID is required.');

        $this->payload->put('document_id', $this->data->id);
    }

    private function downloadPdf(string $url): ?string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $download = Http::get('https://www.moloni.pt/downloads/index.php', [
            'action' => 'getDownload',
            'h' => $query['h'] ?? '',
            'd' => $this->data->id,
        ]);

        if (! $download->successful() || ! str_starts_with((string) $download->header('Content-Type'), 'application/pdf')) {
            return null;
        }

        return base64_encode($download->body());
    }
}
