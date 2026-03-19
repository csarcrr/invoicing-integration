<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldFindClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Client;
use CsarCrr\InvoicingIntegration\Traits\HasPaginator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function collect;

/**
 * Handles paginated client search against the Cegid Vendus API.
 */
class Find extends Client implements ShouldFindClient
{
    use HasPaginator;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @var Collection<int, ClientData> */
    protected Collection $list;

    public function __construct(protected ?ClientData $client = null)
    {
        $this->data = $client ?? ClientData::from([]);
        $this->payload = collect();
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Client);
    }

    /**
     * Sends the search request and populates the result list and pagination details.
     */
    public function execute(): self
    {
        $request = Http::provider()->get('/clients', $this->getPayload());

        Http::handleUnwantedFailures($request);

        $this->updatePaginationDetails($request);
        $this->updateResults($request->json());

        return $this;
    }

    /**
     * @return Collection<int, ClientData>
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
        $this->buildPagination();

        $this->getAllowedProperties()->each(fn (mixed $item, string $key) => $this->payload->put($key, $item));

        $this->buildVat();
        $this->buildExternalReference();
        $this->buildStatus();

        return $this->payload;
    }

    protected function buildPagination(): void
    {
        $this->payload->put('page', $this->getCurrentPage());
    }

    private function buildVat(): void
    {
        (is_string($this->data->vat) || is_int($this->data->vat)) && $this->payload->put('fiscal_id', $this->data->vat);
    }

    private function buildExternalReference(): void
    {
        is_string($this->data->externalReference) && $this->payload->put('external_reference', $this->data->externalReference);
    }

    private function buildStatus(): void
    {
        is_string($this->data->status) && $this->payload->put('status', $this->data->status);
    }

    protected function updatePaginationDetails(Response $results): void
    {
        $this->totalPages((int) $results->header('X-Paginator-Pages') ?: 1);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    protected function updateResults(array $results): void
    {
        $this->list = collect($results)->map(function (array $item) {
            $data = [];

            ! empty($item['id']) && $data['id'] = $item['id'];
            ! empty($item['name']) && $data['name'] = $item['name'];
            ! empty($item['email']) && $data['email'] = $item['email'];
            ! empty($item['fiscal_id']) && $data['vat'] = $item['fiscal_id'];
            ! empty($item['city']) && $data['city'] = $item['city'];
            ! empty($item['address']) && $data['address'] = $item['address'];
            ! empty($item['postalcode']) && $data['postalCode'] = $item['postalcode'];
            ! empty($item['country']) && $data['country'] = $item['country'];
            ! empty($item['default_pay_due']) && $data['defaultPayDue'] = $item['default_pay_due'];
            ! empty($item['notes']) && $data['notes'] = $item['notes'];
            ! empty($item['phone']) && $data['phone'] = $item['phone'];
            ! empty($item['external_reference']) && $data['externalReference'] = $item['external_reference'];
            ! empty($item['send_email']) && $data['emailNotification'] = $item['send_email'];
            ! empty($item['irs_retention']) && $data['irsRetention'] = $item['irs_retention'];
            ! empty($item['status']) && $data['status'] = $item['status'];

            return ClientData::from($data);
        })->values();
    }
}
