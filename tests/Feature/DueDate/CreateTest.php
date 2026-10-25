<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Data\DueDateData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Facades\DueDate;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('creates a due date', function (Provider $provider) {
    if ($provider === Provider::CEGID_VENDUS) {
        $this->markTestSkipped('Cegid Vendus does not support creating due dates.');
    }

    Http::fake(mockResponse(fixtures()->response()->dueDate()->files('create')));

    $dueDate = DueDate::create(DueDateData::make(['name' => '30 dias', 'days' => 30]))->execute()->getDueDate();

    expect($dueDate->id)->toBe(4321)
        ->and($dueDate->name)->toBe('30 dias')
        ->and($dueDate->days)->toBe(30)
        ->and($dueDate->getAdditionalData())->toBe(['valid' => 1]);

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'maturityDates/insert')
        && Str::contains($request->body(), 'days=30'));
})->with('providers');
