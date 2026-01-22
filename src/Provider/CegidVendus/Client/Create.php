<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Create implements CreateClient, ShouldHavePayload
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected ClientDataObject $client)
    {
        $this->payload = collect();
    }

    public function execute(): ClientDataObject
    {
        /** @phpstan-ignore-next-line */
        $response = Http::provider()->post('/clients', $this->getPayload());

        /** @phpstan-ignore-next-line */
        Http::handleUnwantedFailures($response);

        $data = $response->json();

        $this->client->id($data['id']);

        return $this->client;
    }

    /**
     * @return Collection<string, mixed>
     */
    public function getPayload(): Collection
    {
        $this->buildName();
        $this->buildEmail();
        $this->buildCompleteAddress();
        $this->buildVat();
        $this->buildNotes();
        $this->buildIrsRetention();
        $this->buildEmailNotification();
        $this->buildContacts();
        $this->buildDefaultPayDue();

        return $this->payload;
    }

    protected function buildName(): void
    {
        $this->client->getName() && $this->payload->put('name', $this->client->getName());
    }

    protected function buildEmail(): void
    {
        $this->client->getEmail() && $this->payload->put('email', $this->client->getEmail());
    }

    protected function buildCompleteAddress(): void
    {
        $this->client->getAddress() && $this->payload->put('address', $this->client->getAddress());
        $this->client->getCity() && $this->payload->put('city', $this->client->getCity());
        $this->client->getPostalCode() && $this->payload->put('postalcode', $this->client->getPostalCode());
        $this->client->getCountry() && $this->payload->put('country', $this->client->getCountry());
    }

    protected function buildVat(): void
    {
        $this->client->getVat() && $this->payload->put('fiscal_id', $this->client->getVat());
    }

    protected function buildNotes(): void
    {
        $this->client->getNotes() && $this->payload->put('notes', $this->client->getNotes());
    }

    protected function buildIrsRetention(): void
    {
        $this->client->getIrsRetention() ? $this->payload->put('irs_retention', 'yes') : $this->payload->put('irs_retention', 'no');
    }

    protected function buildEmailNotification(): void
    {
        $this->client->getEmailNotification() ? $this->payload->put('send_email', 'yes') : $this->payload->put('send_email', 'no');
    }

    protected function buildContacts(): void
    {
        $this->client->getPhone() && $this->payload->put('phone', $this->client->getPhone());
    }

    protected function buildDefaultPayDue(): void
    {
        $this->client->getDefaultPayDue() && $this->payload->put('default_pay_due', $this->client->getDefaultPayDue());
    }
}
