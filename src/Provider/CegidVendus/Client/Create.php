<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldCreateClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\DueDateTerm;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Helpers\Properties;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Optional;

class Create extends Client implements ShouldCreateClient
{
    /** @var Collection<string, mixed> */
    protected Collection $payload;

    public function __construct(protected ?ClientData $client)
    {
        $this->data = $client;
        $this->payload = collect();
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Client);
    }

    /**
     * @throws \Throwable
     */
    public function execute(): self
    {
        $request = Http::provider()->post('/clients', $this->getPayload());

        Http::handleUnwantedFailures($request);

        $data = $request->json();

        $this->data = $this->toClientData($data);
        $this->fillAdditionalProperties($data);

        return $this;
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
        Properties::isValid($this->data->name) && $this->payload->put('name', $this->data->name);
    }

    protected function buildEmail(): void
    {
        Properties::isValid($this->data->email) && $this->payload->put('email', $this->data->email);
    }

    protected function buildCompleteAddress(): void
    {
        Properties::isValid($this->data->address) && $this->payload->put('address', $this->data->address);
        Properties::isValid($this->data->city) && $this->payload->put('city', $this->data->city);
        Properties::isValid($this->data->postalCode) && $this->payload->put('postalcode', $this->data->postalCode);
        Properties::isValid($this->data->country) && $this->payload->put('country', $this->data->country);
    }

    protected function buildVat(): void
    {
        Properties::isValid($this->data->vat) && $this->payload->put('fiscal_id', $this->data->vat);
    }

    protected function buildNotes(): void
    {
        Properties::isValid($this->data->notes) && $this->payload->put('notes', $this->data->notes);
    }

    protected function buildIrsRetention(): void
    {
        if (! is_bool($this->data->irsRetention)) {
            return;
        }

        $this->payload->put('irs_retention', $this->data->irsRetention ? 'yes' : 'no');
    }

    protected function buildEmailNotification(): void
    {
        if (! is_bool($this->data->emailNotification)) {
            return;
        }

        $this->payload->put('send_email', $this->data->emailNotification ? 'yes' : 'no');
    }

    protected function buildContacts(): void
    {
        Properties::isValid($this->data->phone) && $this->payload->put('phone', $this->data->phone);
    }

    protected function buildDefaultPayDue(): void
    {
        if ($this->data->defaultPayDue instanceof Optional) {
            return;
        }

        $this->payload->put('default_pay_due', $this->data->defaultPayDue === DueDateTerm::Days0 ? 'now' : (string) $this->data->defaultPayDue->value);
    }
}
