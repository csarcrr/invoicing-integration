<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\CreateClient;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\CegidVendusClient;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Optional;

class Create extends CegidVendusClient implements CreateClient, ShouldHavePayload
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected ?ClientData $client)
    {
        $this->payload = collect();
    }

    public function execute(): ClientData
    {
        $response = Http::provider()->post('/clients', $this->getPayload());

        Http::handleUnwantedFailures($response);

        $data = $response->json();

        $this->updateClientData($data);

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
        $this->client->name && $this->payload->put('name', $this->client->name);
    }

    protected function buildEmail(): void
    {
        $this->client->email && $this->payload->put('email', $this->client->email);
    }

    protected function buildCompleteAddress(): void
    {
        $this->client->address && $this->payload->put('address', $this->client->address);
        $this->client->city && $this->payload->put('city', $this->client->city);
        $this->client->postalCode && $this->payload->put('postalcode', $this->client->postalCode);
        $this->client->country && $this->payload->put('country', $this->client->country);
    }

    protected function buildVat(): void
    {
        $this->client->vat && $this->payload->put('fiscal_id', $this->client->vat);
    }

    protected function buildNotes(): void
    {
        $this->client->notes && $this->payload->put('notes', $this->client->notes);
    }

    protected function buildIrsRetention(): void
    {
        $this->client->irsRetention ? $this->payload->put('irs_retention', 'yes') : $this->payload->put('irs_retention', 'no');
    }

    protected function buildEmailNotification(): void
    {
        $this->client->emailNotification ? $this->payload->put('send_email', 'yes') : $this->payload->put('send_email', 'no');
    }

    protected function buildContacts(): void
    {
        $this->client->phone && $this->payload->put('phone', $this->client->phone);
    }

    protected function buildDefaultPayDue(): void
    {
        ! ($this->client->defaultPayDue instanceof Optional) && $this->payload->put('default_pay_due', (string) $this->client->defaultPayDue);
    }
}
