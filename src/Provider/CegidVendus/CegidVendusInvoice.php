<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Provider\CegidVendus;

use AllowDynamicProperties;
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\InvoiceData;
use CsarCrr\InvoicingIntegration\Provider\Base;
use CsarCrr\InvoicingIntegration\ValueObjects\Output;
use Exception;
use Illuminate\Support\Collection;

use function collect;
use function in_array;
use function throw_if;

#[AllowDynamicProperties]
class CegidVendusInvoice extends Base
{
    protected ?InvoiceData $invoice = null;

    /**
     * @var list<string>
     */
    protected array $supportedProperties = ['id', 'type', 'number', 'amount_gross', 'amount_net', 'atcud', 'output'];

    public function getInvoice(): InvoiceData
    {
        return $this->invoice;
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws \Throwable
     */
    protected function fillAdditionalProperties(array $data): void
    {
        $additionalProperties = collect($data)->except($this->supportedProperties)->toArray();
        $this->invoice->additional($additionalProperties);
    }

    /**
     * @return Collection<string, mixed>
     */
    protected function getClientAllowedProperties(): Collection
    {
        return collect($this->client->toArray())->filter(
            fn (mixed $value, string $key) => in_array($key, $this->supportedProperties)
        );
    }
}
