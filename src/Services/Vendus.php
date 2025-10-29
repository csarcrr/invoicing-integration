<?php

namespace CsarCrr\InvoicingIntegration\Services;

use CsarCrr\InvoicingIntegration\InvoicingClient;
use Illuminate\Support\Facades\Http;

class Vendus
{
    protected array $data = [
        'register_id' => null,
        'type' => null,
    ];

    public function __construct(
        protected string $apiKey,
        protected InvoicingClient $client,
        protected array $items
    ) {}

    public function send()
    {
        $this->setDocumentType();
        $this->formatClient();
        $this->formatItems();

        Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
        ])->post('https://www.vendus.pt/api/ws/v1.1/documents/', $this->data);
    }

    protected function setDocumentType()
    {
        $this->data['type'] = 'FT';
    }

    protected function formatClient(): void
    {
        if (! ($this->client instanceof InvoicingClient)) {
            throw new \Exception('Invalid client provided');
        }

        $this->data['client'] = [
            'name' => $this->client->name,
            'fiscal_id' => $this->client->vat,
        ];
    }

    protected function formatItems(): void
    {
        foreach ($this->items as $item) {
            if (! ($item instanceof \CsarCrr\InvoicingIntegration\InvoicingItem)) {
                throw new \Exception('Invalid item provided');
            }

            $this->data['items'][] = [
                'reference' => $item->reference,
                'qty' => $item->quantity,
            ];
        }
    }
}
