<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Provider\Client as BaseClient;

abstract class Client extends BaseClient
{
    /**
     * @param  array<string, mixed>  $client
     */
    protected function toClientData(array $client): ClientData
    {
        $data = [];

        ! empty($client['id']) && $data['id'] = (string) $client['id'];
        ! empty($client['name']) && $data['name'] = $client['name'];
        ! empty($client['email']) && $data['email'] = $client['email'];
        ! empty($client['fiscal_id']) && $data['vat'] = $client['fiscal_id'];
        ! empty($client['city']) && $data['city'] = $client['city'];
        ! empty($client['address']) && $data['address'] = $client['address'];
        ! empty($client['postalcode']) && $data['postalCode'] = $client['postalcode'];
        ! empty($client['country']) && $data['country'] = $client['country'];
        ! empty($client['default_pay_due']) && $data['defaultPayDue'] = (int) $client['default_pay_due'];
        ! empty($client['notes']) && $data['notes'] = $client['notes'];
        ! empty($client['phone']) && $data['phone'] = $client['phone'];
        ! empty($client['external_reference']) && $data['externalReference'] = $client['external_reference'];
        ! empty($client['send_email']) && $data['emailNotification'] = $client['send_email'] === 'yes';
        ! empty($client['irs_retention']) && $data['irsRetention'] = $client['irs_retention'] === 'yes';
        ! empty($client['status']) && $data['status'] = $client['status'];
        ! empty($client['date']) && $data['date'] = $client['date'];

        return ClientData::from($data);
    }
}
