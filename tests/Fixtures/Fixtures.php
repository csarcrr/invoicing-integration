<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Tests\Fixtures;

use CsarCrr\InvoicingIntegration\Enums\Provider;
use Illuminate\Support\Facades\File;
use RuntimeException;

class Fixtures
{
    protected array $path = [];

    public function __construct(protected Provider $provider)
    {
        $this->path = [];
    }

    public static function build(Provider $provider): self
    {
        return new self($provider);
    }

    public function response(): self
    {
        $this->addPath('Response');

        return $this;
    }

    public function request(): self
    {
        $this->addPath('Request');

        return $this;
    }

    public function addPath(string $path): self
    {
        $this->path[] = $path;

        return $this;
    }

    public function relatedDocument(): self
    {
        $this->path[] = 'RelatedDocument';

        return $this;
    }

    public function invoice(): self
    {
        $this->path[] = 'Invoice';

        return $this;
    }

    public function invoiceTypes(): self
    {
        $this->path[] = 'InvoiceTypes';

        return $this;
    }

    public function type(): self
    {
        $this->path[] = 'Type';

        return $this;
    }

    public function client(): self
    {
        $this->path[] = 'Client';

        return $this;
    }

    public function item(): self
    {
        $this->path[] = 'Item';

        return $this;
    }

    public function dueDate(): self
    {
        $this->path[] = 'DueDate';

        return $this;
    }

    public function itemStock(): self
    {
        $this->path[] = 'ItemStock';

        return $this;
    }

    public function company(): self
    {
        $this->path[] = 'Company';

        return $this;
    }

    public function unit(): self
    {
        $this->path[] = 'Unit';

        return $this;
    }

    public function category(): self
    {
        $this->path[] = 'Category';

        return $this;
    }

    public function warehouse(): self
    {
        $this->path[] = 'Warehouse';

        return $this;
    }

    public function taxExemption(): self
    {
        $this->path[] = 'TaxExemption';

        return $this;
    }

    public function paymentMethod(): self
    {
        $this->path[] = 'PaymentMethod';

        return $this;
    }

    public function documentSeries(): self
    {
        $this->path[] = 'DocumentSeries';

        return $this;
    }

    public function country(): self
    {
        $this->path[] = 'Country';

        return $this;
    }

    public function language(): self
    {
        $this->path[] = 'Language';

        return $this;
    }

    public function tax(): self
    {
        $this->path[] = 'Tax';

        return $this;
    }

    public function transport(): self
    {
        $this->path[] = 'Transport';

        return $this;
    }

    public function output(): self
    {
        $this->path[] = 'Output';

        return $this;
    }

    public function payment(): self
    {
        $this->path[] = 'Payment';

        return $this;
    }

    public function files(string $name): array
    {
        $file = __DIR__."/IntegrationProvider/{$this->provider->value}/".implode('/', $this->path)."/{$name}.json";

        if (! is_file($file)) {
            throw new RuntimeException("Fixture file not found: {$file}");
        }

        return collect(File::json($file))->sortKeys()->toArray();
    }
}
