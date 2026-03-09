<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Client;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Client\ShouldGetClient;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Enums\Property;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Provider\Client;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

use function is_int;
use function throw_if;

/**
 * Handles retrieval of a single client by ID from the Cegid Vendus API.
 */
class Find extends Client implements ShouldGetClient
{
    public function __construct(protected ?ClientData $client)
    {
        $this->data = $client;
        $this->supportedProperties = Provider::CEGID_VENDUS->supportedProperties(Property::Client);
    }

    /**
     * @throws InvalidArgumentException|\Throwable
     */
    public function execute(): self
    {
        throw_if(! is_int($this->data->id), InvalidArgumentException::class, 'Client ID is required.');

        $request = Http::provider()->get('/clients/'.$this->data->id);

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
}
