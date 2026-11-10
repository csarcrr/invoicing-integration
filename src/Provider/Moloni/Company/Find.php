<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Company;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Company\ShouldFindCompany;
use CsarCrr\InvoicingIntegration\Data\CompanyData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

class Find implements ShouldFindCompany
{
    /** @var Collection<int, CompanyData> */
    protected Collection $list;

    public function __construct()
    {
        $this->list = collect();
    }

    public function execute(): self
    {
        $request = Http::provider()->post('companies/getAll');

        Http::handleUnwantedFailures($request);

        /** @var array<int, array<string, mixed>> $results */
        $results = $request->json();

        $this->list = collect($results)->map(fn (array $company) => CompanyData::make([
            'id' => $company['company_id'],
            'name' => $company['name'],
            'vat' => $company['vat'] ?? null,
        ]))->values();

        return $this;
    }

    /**
     * @return Collection<int, CompanyData>
     */
    public function getList(): Collection
    {
        return $this->list;
    }
}
