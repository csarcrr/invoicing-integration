<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldCreateClient;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Optional;

/**
 * Handles client creation against the Cegid Vendus API.
 */
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
     * Sends the create request and populates the client DTO with the API response.
     *
     * @throws \Throwable
     */
    public function execute(): self
    {
        $request = Http::provider()->post('/clients', $this->getPayload());

        Http::handleUnwantedFailures($request);

        $data = $request->json();
        $this->fillAdditionalProperties($data);

        ! empty($data['postalcode']) && $data['postalCode'] = $data['postalcode'];
        ! empty($data['default_pay_due']) && $data['defaultPayDue'] = $data['default_pay_due'];
        ! empty($data['fiscal_id']) && $data['vat'] = $data['fiscal_id'];
        ! empty($data['send_email']) && $data['email_notification'] = $data['send_email'] === 'yes';
        ! empty($data['irs_retention']) && $data['irs_retention'] = $data['irs_retention'] === 'yes';

        $this->data = $this->data->from($data);

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
        $this->data->name && $this->payload->put('name', $this->data->name);
    }

    protected function buildEmail(): void
    {
        $this->data->email && $this->payload->put('email', $this->data->email);
    }

    protected function buildCompleteAddress(): void
    {
        $this->data->address && $this->payload->put('address', $this->data->address);
        $this->data->city && $this->payload->put('city', $this->data->city);
        $this->data->postalCode && $this->payload->put('postalcode', $this->data->postalCode);
        $this->data->country && $this->payload->put('country', $this->data->country);
    }

    protected function buildVat(): void
    {
        $this->data->vat && $this->payload->put('fiscal_id', $this->data->vat);
    }

    protected function buildNotes(): void
    {
        $this->data->notes && $this->payload->put('notes', $this->data->notes);
    }

    protected function buildIrsRetention(): void
    {
        $this->data->irsRetention ? $this->payload->put('irs_retention', 'yes') : $this->payload->put('irs_retention', 'no');
    }

    protected function buildEmailNotification(): void
    {
        $this->data->emailNotification ? $this->payload->put('send_email', 'yes') : $this->payload->put('send_email', 'no');
    }

    protected function buildContacts(): void
    {
        $this->data->phone && $this->payload->put('phone', $this->data->phone);
    }

    protected function buildDefaultPayDue(): void
    {
        ! ($this->data->defaultPayDue instanceof Optional) && $this->payload->put('default_pay_due', (string) $this->data->defaultPayDue);
    }
}
