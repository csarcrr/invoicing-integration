<?php

declare(strict_types=1);

use CsarCrr\InvoicingIntegration\Enums\Provider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->envFile = tempnam(sys_get_temp_dir(), 'invoicing-integration-env');
});

afterEach(function () {
    @unlink($this->envFile);
});

function setupEnv(string $path): string
{
    return (string) file_get_contents($path);
}

function fakeMoloniSetup(?array $taxes = null, ?array $taxInsert = null): void
{
    Http::fake([
        '*companies/getAll*' => mockResponse(fixtures()->response()->company()->files('response_multiple')),
        '*customers/getByVat*' => mockResponse([['customer_id' => 777, 'name' => 'Consumidor Final', 'vat' => '999999990']]),
        '*taxes/getAll*' => mockResponse($taxes ?? fixtures()->response()->tax()->files('response_multiple')),
        '*taxes/insert*' => mockResponse($taxInsert ?? fixtures()->response()->tax()->files('create')),
        '*measurementUnits/getAll*' => mockResponse(fixtures()->response()->unit()->files('response_multiple')),
        '*taxExemptions/getAll*' => mockResponse(fixtures()->response()->taxExemption()->files('response_multiple')),
    ]);
}

function moloniSetupCommand(mixed $test, string $password = 'secret-password'): mixed
{
    return $test->artisan('invoicing-integration:setup', ['--env-file' => $test->envFile])
        ->expectsQuestion('Which provider do you want to set up?', 'Moloni')
        ->expectsQuestion('Moloni developer ID', 'dev-1')
        ->expectsQuestion('Moloni client secret', 'client-secret')
        ->expectsQuestion('Moloni username', 'user@example.com')
        ->expectsQuestion('Moloni password', $password)
        ->expectsQuestion('Which Moloni company?', '5 - Empresa Demonstração');
}

test('writes the Cegid Vendus ids to the env file', function () {
    mockConfiguration(Provider::CEGID_VENDUS);

    Http::fake([
        '*documents/paymentmethods*' => mockResponse(fixtures()->response()->paymentMethod()->files('response_multiple')),
        '*products/units*' => mockResponse(fixtures()->response()->unit()->files('response_multiple')),
    ]);

    $this->artisan('invoicing-integration:setup', ['--env-file' => $this->envFile])
        ->expectsQuestion('Which provider do you want to set up?', 'CegidVendus')
        ->expectsQuestion('Cegid Vendus API key', 'key-123')
        ->expectsQuestion('Cegid Vendus mode', 'normal')
        ->assertSuccessful();

    expect(setupEnv($this->envFile))
        ->toContain("INVOICING_INTEGRATION_PROVIDER=CegidVendus\n")
        ->toContain("CEGID_VENDUS_API_KEY=key-123\n")
        ->toContain("CEGID_VENDUS_MODE=normal\n")
        ->toContain("CEGID_VENDUS_PAYMENT_MONEY_ID=7001\n")
        ->toContain("CEGID_VENDUS_PAYMENT_MB_ID=7002\n")
        ->toContain("CEGID_VENDUS_PAYMENT_CREDIT_CARD_ID=7003\n")
        ->toContain("CEGID_VENDUS_PAYMENT_MONEY_TRANSFER_ID=7004\n")
        ->toContain("CEGID_VENDUS_PAYMENT_CURRENT_ACCOUNT_ID=7006\n")
        ->toContain("CEGID_VENDUS_UNIT_KG_ID=19999\n")
        ->toContain("CEGID_VENDUS_UNIT_UNIT_ID=29999\n");
});

test('writes the Moloni ids to the env file', function () {
    mockConfiguration(Provider::MOLONI);
    fakeMoloniSetup();

    moloniSetupCommand($this)
        ->expectsOutputToContain('No tax found for INTERMEDIATE')
        ->assertSuccessful();

    expect(setupEnv($this->envFile))
        ->toContain("INVOICING_INTEGRATION_PROVIDER=Moloni\n")
        ->toContain("MOLONI_DEVELOPER_ID=dev-1\n")
        ->toContain("MOLONI_CLIENT_SECRET=client-secret\n")
        ->toContain("MOLONI_USERNAME=user@example.com\n")
        ->toContain("MOLONI_PASSWORD=secret-password\n")
        ->toContain("MOLONI_COMPANY_ID=5\n")
        ->toContain("MOLONI_NO_VAT_CLIENT_ID=777\n")
        ->toContain("MOLONI_TAX_NORMAL_ID=3951102\n")
        ->toContain("MOLONI_TAX_REDUCED_ID=3951103\n")
        ->toContain("MOLONI_TAX_EXEMPT_ID=3951900\n")
        ->toContain("MOLONI_UNIT_KG_ID=19999\n")
        ->toContain("MOLONI_UNIT_UNIT_ID=29999\n")
        ->not->toContain('MOLONI_TAX_INTERMEDIATE_ID')
        ->not->toContain('MOLONI_TAX_OTHER_ID');
});

test('sends the selected company on the Moloni requests after picking it', function () {
    mockConfiguration(Provider::MOLONI);
    fakeMoloniSetup();

    moloniSetupCommand($this)->assertSuccessful();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'taxes/getAll')
        && Str::contains($request->body(), 'company_id=5'));
});

test('creates the Moloni exempt tax when the account has none', function () {
    mockConfiguration(Provider::MOLONI);
    fakeMoloniSetup(collect(fixtures()->response()->tax()->files('response_multiple'))
        ->reject(fn (array $tax) => $tax['vat_type'] === 'ISE')
        ->values()
        ->toArray());

    moloniSetupCommand($this)
        ->expectsQuestion('Exemption reason for the new exempt tax', 'M07')
        ->assertSuccessful();

    Http::assertSent(fn (Request $request) => Str::contains($request->url(), 'taxes/insert')
        && Str::contains($request->body(), 'vat_type=ISE')
        && Str::contains($request->body(), 'exemption_reason=M07')
        && Str::contains($request->body(), 'fiscal_zone=PT'));

    expect(setupEnv($this->envFile))->toContain("MOLONI_TAX_EXEMPT_ID=3951900\n");
});

test('warns and keeps going when Moloni rejects the exempt tax', function () {
    mockConfiguration(Provider::MOLONI);
    fakeMoloniSetup(
        collect(fixtures()->response()->tax()->files('response_multiple'))
            ->reject(fn (array $tax) => $tax['vat_type'] === 'ISE')
            ->values()
            ->toArray(),
        [['code' => '2 value 0 null null 0 100', 'description' => "Field 'value' must be float, greater or equal to 0, lesser or equal to 100"]],
    );

    moloniSetupCommand($this)
        ->expectsQuestion('Exemption reason for the new exempt tax', 'M07')
        ->expectsOutputToContain('Moloni rejected the exempt tax')
        ->assertSuccessful();

    expect(setupEnv($this->envFile))
        ->toContain("MOLONI_TAX_NORMAL_ID=3951102\n")
        ->not->toContain('MOLONI_TAX_EXEMPT_ID');
});

test('asks which tax to use when several match', function () {
    mockConfiguration(Provider::MOLONI);
    fakeMoloniSetup([
        ...fixtures()->response()->tax()->files('response_multiple'),
        ['tax_id' => 3951753, 'name' => 'Normal (Continente)', 'value' => 23, 'vat_type' => 'NOR', 'exemption_reason' => '', 'fiscal_zone' => 'PT'],
    ]);

    moloniSetupCommand($this)
        ->expectsQuestion('Which tax for NORMAL?', '3951753 - Normal (Continente) (23%)')
        ->assertSuccessful();

    expect(setupEnv($this->envFile))->toContain("MOLONI_TAX_NORMAL_ID=3951753\n");
});

test('replaces the existing keys and keeps the other lines of the env file', function () {
    mockConfiguration(Provider::CEGID_VENDUS);
    file_put_contents($this->envFile, "APP_NAME=Test\nCEGID_VENDUS_API_KEY=old-key\n");

    Http::fake([
        '*documents/paymentmethods*' => mockResponse(fixtures()->response()->paymentMethod()->files('response_multiple')),
        '*products/units*' => mockResponse(fixtures()->response()->unit()->files('response_multiple')),
    ]);

    $this->artisan('invoicing-integration:setup', ['--env-file' => $this->envFile])
        ->expectsQuestion('Which provider do you want to set up?', 'CegidVendus')
        ->expectsQuestion('Cegid Vendus API key', 'new-key')
        ->expectsQuestion('Cegid Vendus mode', 'tests')
        ->assertSuccessful();

    $env = setupEnv($this->envFile);

    expect($env)->toStartWith("APP_NAME=Test\nCEGID_VENDUS_API_KEY=new-key\n")
        ->and(substr_count($env, 'CEGID_VENDUS_API_KEY='))->toBe(1)
        ->and($env)->not->toContain('old-key');
});

test('quotes values that are not plain words', function () {
    mockConfiguration(Provider::MOLONI);
    fakeMoloniSetup();

    moloniSetupCommand($this, 'my pass$1"')->assertSuccessful();

    expect(setupEnv($this->envFile))->toContain('MOLONI_PASSWORD="my pass\$1\""'."\n");
});
