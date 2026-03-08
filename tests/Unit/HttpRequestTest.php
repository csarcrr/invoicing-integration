<?php

use CsarCrr\InvoicingIntegration\Exceptions\Providers\FailedReachingProviderException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\UnauthorizedException;
use Illuminate\Support\Facades\Http;

test('fails when request is not authorized', function () {
    Http::fake(mockResponse([], 401));

    $response = Http::post('http://provider.site/example');

    Http::handleUnwantedFailures($response);
})->throws(UnauthorizedException::class);

it('fails when provider has server error', function () {
    Http::fake(mockResponse([], 500));

    $response = Http::post('http://provider.site/example');

    Http::handleUnwantedFailures($response);
})->throws(FailedReachingProviderException::class);

it('fails with error messages when it is possible to identify errors in the response payload', function () {
    Http::fake(mockResponse([
        'errors' => [
            [
                'message' => 'Generic error',
            ],
        ],
    ], 400));

    $response = Http::post('http://provider.site/example');

    Http::handleUnwantedFailures($response);
})->throws(RequestFailedException::class);

it('fails with generic error when type of error was not possible to identify', function () {
    Http::fake(mockResponse([], 405));

    $response = Http::post('http://provider.site/example');

    Http::handleUnwantedFailures($response);
})->throws(Exception::class);
