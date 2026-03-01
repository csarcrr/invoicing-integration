<?php

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus\Item;

use CsarCrr\InvoicingIntegration\Contracts\IntegrationProvider\Item\ShouldCreateItem;
use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Provider\CegidVendus\CegidVendusItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Create extends CegidVendusItem implements ShouldCreateItem, ShouldExecute
{
    public function __construct(protected ?ItemData $item) {

    }
    public function execute(): ShouldExecute
    {
        $response = Http::provider()->post('products', $this->getPayload());
    }

    public function getPayload(): Collection
    {
        // TODO: Implement getPayload() method.
    }

    public function getClient(): ClientData
    {
        // TODO: Implement getClient() method.
    }
}
