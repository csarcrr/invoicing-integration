<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Language;

use CsarCrr\InvoicingIntegration\Data\LanguageData;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\CouldNotGetLanguageIdException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use function collect;
use function throw_if;

class Get
{
    public function __construct(protected LanguageData $language) {}

    /**
     * @throws CouldNotGetLanguageIdException
     */
    public function execute(): self
    {
        $response = Http::provider()->post('languages/getAll');

        Http::handleUnwantedFailures($response);

        /** @var array<int, array<string, mixed>> $languages */
        $languages = $response->json();

        $match = collect($languages)->first(
            fn (array $language): bool => Str::lower((string) $language['code']) === Str::lower($this->language->code)
        );

        throw_if(is_null($match), CouldNotGetLanguageIdException::class);

        $this->language->id = (int) $match['language_id'];
        $this->language->name = (string) $match['title'];

        return $this;
    }

    public function getLanguage(): LanguageData
    {
        return $this->language;
    }
}
