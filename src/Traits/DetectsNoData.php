<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

use Illuminate\Http\Client\Response;

use function collect;

trait DetectsNoData
{
    protected function hasNoData(Response $response): bool
    {
        return ($response->successful() && empty($response->json()))
            || ($response->notFound() && collect((array) $response->json('errors', []))->contains('code', 'A001'));
    }
}
