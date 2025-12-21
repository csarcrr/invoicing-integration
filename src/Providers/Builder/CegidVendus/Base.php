<?php 

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Providers\Builder\CegidVendus;

use CsarCrr\InvoicingIntegration\Exceptions\Providers\CegidVendus\RequestFailedException;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

abstract class Base {
    private array $provider = [];

    protected string $method = 'GET';
    protected array $headers = [];

    private ?string $endpoint = null;
    private ?string $baseUrl = null;

    public function __construct () 
    {
        $this->setProvider(config('invoicing-integration.providers')['CegidVendus'] ?? []);
        $this->setupURL();
        $this->setupAuthenticationHeader();
    }

    protected function providerOptions(): Collection {
        return collect($this->provider['config']);
    }

    protected function endpoint (): string 
    {
        return $this->endpoint;
    }

    protected function headers (): array 
    {
        return $this->headers;
    }

    protected function setEndpoint (string $endpoint): void {
        $this->endpoint = $endpoint;
    }

    protected function request (): array {
        $request = Http::withHeaders($this->headers())->post(
            $this->baseUrl . $this->endpoint(),
            $this->payload()->toArray()
        );

        if (! in_array($request->status(), [200, 201, 300, 301])) {
            $this->throwErrors($request->json());
        }

        return $request->json();
    }

    private function throwErrors(array $errors): void
    {
        $messages = collect($errors['errors'] ?? [])->map(function ($error) {
            return $error['message'] ? $error['code'] . ' - ' . $error['message'] : 'Unknown error';
        })->toArray();

        throw_if(! empty($messages), RequestFailedException::class, implode('; ', $messages));

        throw new Exception('The integration API request failed for an unknown reason.');
    }

    private function setProvider(array $provider): void
    {
        $this->provider = $provider;
    }

    private function setupURL(): void
    {
        $this->baseUrl = 'https://www.vendus.pt/ws/v1.1';
    }

    private function setupAuthenticationHeader(): void
    {
        $this->headers['Authorization'] = 'Bearer ' . ($this->provider['key'] ?? '');
    }

    abstract protected function payload() : Collection;
    
}