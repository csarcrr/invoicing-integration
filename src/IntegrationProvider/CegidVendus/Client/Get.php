<?php

namespace CsarCrr\InvoicingIntegration\IntegrationProvider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\GetClient;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function throw_if;

class Get implements GetClient
{
    public function __construct(protected ClientDataObject $client) {}

    public function execute(): ClientDataObject
    {
        throw_if(! $this->client->getId(), InvalidArgumentException::class, 'Client ID is required.');

        /** @phpstan-ignore-next-line */
        $request = Http::provider()->get('/clients/'.$this->client->getId());
        /** @phpstan-ignore-next-line */
        Http::handleUnwantedFailures($request);

        $this->fill($request->json());

        return $this->client;
    }

    /** @param array<string, mixed> $data */
    protected function fill(array $data): void
    {
        ! empty($data['name']) && $this->client->name($data['name']);
        ! empty($data['email']) && $this->client->email($data['email']);
        ! empty($data['address']) && $this->client->address($data['address']);
        ! empty($data['phone']) && $this->client->phone($data['phone']);
        ! empty($data['notes']) && $this->client->notes($data['notes']);
        ! empty($data['postalcode']) && $this->client->postalCode($data['postalcode']);
        ! empty($data['fiscal_id']) && $this->client->vat($data['fiscal_id']);
        ! empty($data['city']) && $this->client->city($data['city']);
        ! empty($data['country']) && $this->client->country($data['country']);
        ! empty($data['irs_retention']) && $this->client->irsRetention($data['irs_retention']);
        ! empty($data['send_email']) && $this->client->emailNotification($data['send_email']);
        ! empty($data['default_pay_due']) && $this->client->defaultPayDue($data['default_pay_due']);
    }
}
