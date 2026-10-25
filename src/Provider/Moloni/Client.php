<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\Moloni;

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Provider\Client as BaseClient;
use Illuminate\Support\Str;

use function collect;

abstract class Client extends BaseClient
{
    /**
     * @param  array<string, mixed>  $client
     */
    protected function toClientData(?string $id, array $client): ClientData
    {
        return ClientData::from(collect([
            'id' => $id,
            'name' => $client['name'] ?? null,
            'vat' => $client['vat'] ?? null,
            'address' => $client['address'] ?? null,
            'city' => $client['city'] ?? null,
            'postal_code' => $client['zip_code'] ?? null,
            'email' => $client['email'] ?? null,
            'phone' => $client['phone'] ?? null,
            'notes' => $client['notes'] ?? null,
            'country' => Str::upper($client['country']['iso_3166_1'] ?? ''),
        ])->filter()->toArray());
    }
}
