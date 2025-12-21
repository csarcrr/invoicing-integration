<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Facades\Invoice;
use CsarCrr\InvoicingIntegration\ValueObjects\Client;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->client = new Client;
    $this->invoice = Invoice::create();
});

it('sets and gets client email', function () {
    $this->client->setEmail('email@mail.com');
    expect($this->client->email())->toBe('email@mail.com');
});

it('fails when e-mail is not valid', function () {
    $this->client->setEmail('invalid');
})->throws(ValidationException::class, 'The email field must be a valid email address.');
