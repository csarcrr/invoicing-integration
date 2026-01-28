<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\ValueObjects\ClientData;
use Exception;
use function throw_if;

#[AllowDynamicProperties]
class CegidVendusClient
{
    protected ?ClientData $client = null;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateClientData(array $data): void
    {
        throw_if(empty($this->client), Exception::class, 'Client not set');

        ! empty($data['postalcode']) && $data['postalCode'] = $data['postalcode'];
        ! empty($data['default_pay_due']) && $data['defaultPayDue'] = $data['default_pay_due'];
        ! empty($data['fiscal_id']) && $data['vat'] = $data['fiscal_id'];
        ! empty($data['send_email']) && $data['email_notification'] = $data['send_email'] === 'yes';
        ! empty($data['irs_retention']) && $data['irs_retention'] = $data['irs_retention'] === 'yes';

        $this->client = $this->client->from($data);
        $this->client->additional($data);
    }
}
