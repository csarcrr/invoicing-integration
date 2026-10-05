<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldCreateClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\Moloni\MissingClientDataException;
use CsarCrr\InvoicingIntegration\Helpers\Properties;
use CsarCrr\InvoicingIntegration\Provider\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use function collect;
use function throw_if;

class Create extends Client implements ShouldCreateClient
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected ?ClientData $client)
    {
        $this->data = $client;
        $this->payload = collect();
        $this->supportedProperties = Provider::MOLONI->supportedProperties(Property::Client);
    }

    /**
     * @throws MissingClientDataException
     */
    public function execute(): self
    {
        $payload = $this->getPayload();

        $response = Http::provider()->post('customers/insert', $payload->toArray());

        Http::handleUnwantedFailures($response);

        $this->data = ClientData::from([
            'id' => $payload->get('number'),
        ] + $this->data->toArray());

        $this->fillAdditionalProperties($response->json());

        return $this;
    }

    /**
     * @return Collection<string, mixed>
     *
     * @throws MissingClientDataException
     */
    public function getPayload(): Collection
    {
        $this->ensureRequiredData();

        $this->buildNumber();
        $this->buildName();
        $this->buildVat();
        $this->buildAddress();
        $this->buildContacts();
        $this->buildNotes();
        $this->buildPendingIds();

        return $this->payload;
    }

    /**
     * @throws MissingClientDataException
     */
    protected function ensureRequiredData(): void
    {
        foreach (['name', 'vat', 'address', 'city'] as $field) {
            throw_if(
                Properties::isNotValid($this->data->{$field}),
                MissingClientDataException::class,
                "Moloni requires the {$field} to create a client."
            );
        }
    }

    protected function buildNumber(): void
    {
        $number = Properties::isValid($this->data->id)
            ? $this->data->id
            : $this->data->vat.Str::random(8);

        $this->payload->put('number', $number);
    }

    protected function buildName(): void
    {
        $this->payload->put('name', $this->data->name);
    }

    protected function buildVat(): void
    {
        $this->payload->put('vat', (string) $this->data->vat);
    }

    protected function buildAddress(): void
    {
        $this->payload->put('address', $this->data->address);
        $this->payload->put('city', $this->data->city);

        if (Properties::isNotValid($this->data->postalCode)) {
            return;
        }

        $this->payload->put('zip_code', $this->data->postalCode);
    }

    protected function buildContacts(): void
    {
        Properties::isValid($this->data->email) && $this->payload->put('email', $this->data->email);
        Properties::isValid($this->data->phone) && $this->payload->put('phone', $this->data->phone);
    }

    protected function buildNotes(): void
    {
        if (Properties::isNotValid($this->data->notes)) {
            return;
        }

        $this->payload->put('notes', $this->data->notes);
    }

    protected function buildPendingIds(): void
    {
        $this->payload->put('country_id', 0);
        $this->payload->put('language_id', 0);
        $this->payload->put('maturity_date_id', 0);
        $this->payload->put('payment_method_id', 0);
    }
}
