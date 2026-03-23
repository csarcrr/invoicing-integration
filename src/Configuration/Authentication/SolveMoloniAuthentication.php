<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Configuration\Authentication;

use CsarCrr\InvoicingIntegration\Contracts\ShouldExecute;
use CsarCrr\InvoicingIntegration\Contracts\ShouldHavePayload;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SolveMoloniAuthentication implements ShouldExecute, ShouldHavePayload
{
    protected string $cacheKey = 'invoicing_integration_moloni_access_token';

    protected int $refreshBufferSeconds = 300;

    /** @var Collection<string, mixed> */
    protected Collection $payload;

    /** @param array<string, mixed> $config */
    public function __construct(protected array $config)
    {
        /** @var Collection<string, mixed> $payload */
        $payload = collect([]);
        $this->payload = $payload;
    }

    public function execute(): static
    {
        /** @var array<string, mixed>|null $cached */
        $cached = Cache::get($this->cacheKey);

        if (! is_null($cached)) {
            /** @var Collection<string, mixed> $payload */
            $payload = collect($cached);
            $this->payload = $payload;

            return $this;
        }

        $response = $this->fetch();

        $this->cacheToken((string) $response['access_token'], (int) $response['expires_in']);

        /** @var Collection<string, mixed> $payload */
        $payload = collect($response);
        $this->payload = $payload;

        return $this;
    }

    /** @return Collection<string, mixed> */
    public function getPayload(): Collection
    {
        return $this->payload;
    }

    /** @return array<string, mixed> */
    protected function fetch(): array
    {
        /** @var array<string, mixed> */
        return Http::get($this->buildGrantUrl())->json();
    }

    protected function buildGrantUrl(): string
    {
        return 'https://api.moloni.pt/v1/grant/?'.http_build_query([
            'grant_type' => 'authorization_code',
            'client_id' => $this->config['developer_id'],
            'redirect_uri' => $this->config['callback_url'],
            'client_secret' => $this->config['client_secret'],
            'code' => $this->config['authorization_code'],
        ]);
    }

    protected function cacheToken(string $token, int $expiresIn): void
    {
        Cache::put(
            $this->cacheKey,
            ['access_token' => $token],
            $expiresIn - $this->refreshBufferSeconds,
        );
    }
}
