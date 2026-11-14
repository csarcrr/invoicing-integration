<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\DocumentType;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\DocumentType\ShouldFindDocumentType;
use CsarCrr\InvoicingIntegration\Data\DocumentTypeData;
use CsarCrr\InvoicingIntegration\Data\LanguageData;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetLanguageIdException;
use CsarCrr\InvoicingIntegration\Provider\Moloni\DocumentType;
use CsarCrr\InvoicingIntegration\Provider\Moloni\Language\Get as GetLanguage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find extends DocumentType implements ShouldFindDocumentType
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, DocumentTypeData> */
    protected Collection $list;

    public function __construct(protected ?LanguageData $language = null)
    {
        $this->payload = collect();
        $this->list = collect();

        $this->buildUrl();
    }

    /**
     * @throws CouldNotGetLanguageIdException
     */
    public function execute(): self
    {
        $this->buildLanguage();

        $response = Http::provider()->post($this->getUrl(), $this->getPayload()->toArray());

        Http::handleUnwantedFailures($response);

        /** @var array<int, array<string, mixed>> $results */
        $results = $response->json();

        $this->list = collect($results)->map(fn (array $type) => $this->toDocumentTypeData($type))->values();

        return $this;
    }

    /**
     * @return Collection<int, DocumentTypeData>
     */
    public function getList(): Collection
    {
        return $this->list;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        return $this->payload;
    }

    protected function buildLanguage(): void
    {
        $language = (new GetLanguage($this->language ?? LanguageData::from(['code' => 'pt'])))->execute()->getLanguage();

        $this->payload->put('language_id', $language->id);
    }

    protected function buildUrl(): void
    {
        $this->url = 'documents/getAllDocumentTypes';
    }
}
