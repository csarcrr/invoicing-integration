<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Tests\Fixtures;

use CsarCrr\InvoicingIntegration\Enums\IntegrationProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Fixtures
{
    protected array $path = [];

    public function __construct(protected IntegrationProvider $provider) {
        $this->path = [];
    }

    static public function request(IntegrationProvider $provider): self
    {
        $class = new self($provider);
        $class->addPath('Request');
        return $class;
    }

    public function addPath(string $path): self
    {
        $this->path[] = $path;

        return $this;
    }

    public function invoice(): self
    {
        $this->path[] = 'Invoice';

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

    public function transport(): self
    {
        $this->path[] = 'Transport';
        return $this;
    }

    public function payment(): self
    {
        $this->path[] = 'Payment';
        return $this;
    }

    public function files(?string $name = null): array
    {
        $basePath = __DIR__ . '/IntegrationProvider/';
        $filesPath = "{$basePath}{$this->provider->value}/" . implode('/', $this->path) . '/';

        $files = [];

        foreach (scandir($filesPath) as $file) {
            if (str_ends_with($file, '.json') && is_file($filesPath . $file)) {
                $key = str_replace('.json', '', $file);

                $files[$key] = File::json($filesPath . $file);
            }
        }

        return $name ? $files[$name] : $files;
    }
}
