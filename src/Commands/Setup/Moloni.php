<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Commands\Setup;

use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\CompanyData;
use CsarCrr\InvoicingIntegration\Data\TaxData;
use CsarCrr\InvoicingIntegration\Enums\Provider;
use CsarCrr\InvoicingIntegration\Enums\Tax\ItemTax;
use CsarCrr\InvoicingIntegration\Enums\Tax\TaxExemptionReason;
use CsarCrr\InvoicingIntegration\Exceptions\Providers\RequestFailedException;
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\Facades\Company;
use CsarCrr\InvoicingIntegration\Facades\Tax;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Moloni extends ProviderSetup
{
    protected string $fiscalZone = 'PT';

    protected string $noVatNumber = '999999990';

    public function run(Command $command): array
    {
        $credentials = [
            'developer_id' => (string) $command->ask('Moloni developer ID'),
            'client_secret' => (string) $command->secret('Moloni client secret'),
            'username' => (string) $command->ask('Moloni username'),
            'password' => (string) $command->secret('Moloni password'),
        ];

        Cache::forget('invoicing_integration_moloni_access_token');
        $this->configure(Provider::MOLONI, [...$credentials, 'company_id' => null]);

        $company = $this->pick(
            $command,
            'Which Moloni company?',
            Company::find()->execute()->getList(),
            fn (CompanyData $data) => "{$data->id} - {$data->name}",
        );

        if (is_null($company)) {
            $this->missing($command, 'No Moloni company found.');

            return $this->credentialKeys($credentials);
        }

        $this->configure(Provider::MOLONI, [...$credentials, 'company_id' => $company->id]);

        return [
            ...$this->credentialKeys($credentials),
            'MOLONI_COMPANY_ID' => (string) $company->id,
            ...$this->noVatClientId($command),
            ...$this->taxIds($command),
            ...$this->unitIds($command, 'MOLONI'),
        ];
    }

    /**
     * @param  array<string, string>  $credentials
     * @return array<string, string>
     */
    protected function credentialKeys(array $credentials): array
    {
        return collect($credentials)
            ->mapWithKeys(fn (string $value, string $key) => ['MOLONI_'.strtoupper($key) => $value])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function noVatClientId(Command $command): array
    {
        $client = Client::find(ClientData::from(['vat' => $this->noVatNumber]))->execute()->getList()->first();

        if (is_null($client)) {
            $this->missing($command, "No client with VAT {$this->noVatNumber} found.");

            return [];
        }

        return ['MOLONI_NO_VAT_CLIENT_ID' => (string) $client->id];
    }

    /**
     * @return array<string, string>
     */
    protected function taxIds(Command $command): array
    {
        $taxes = Tax::find()->execute()->getList()->filter(fn (TaxData $tax) => $tax->fiscalZone === $this->fiscalZone);

        $ids = collect([ItemTax::NORMAL, ItemTax::INTERMEDIATE, ItemTax::REDUCED, ItemTax::EXEMPT])
            ->mapWithKeys(fn (ItemTax $type) => [$type->name => $this->pickTax($command, $taxes, $type)
                ?? ($type === ItemTax::EXEMPT ? $this->createExemptTax($command) : $this->missing($command, "No tax found for {$type->name}."))])
            ->filter()
            ->mapWithKeys(fn (TaxData $tax, string $type) => ["MOLONI_TAX_{$type}_ID" => (string) $tax->id])
            ->all();

        return [...$ids, ...$this->otherTax($command, $taxes)];
    }

    /**
     * @param  Collection<int, TaxData>  $taxes
     */
    protected function pickTax(Command $command, Collection $taxes, ItemTax $type): ?TaxData
    {
        return $this->pick(
            $command,
            "Which tax for {$type->name}?",
            $taxes->filter(fn (TaxData $tax) => $tax->type === $type)->values(),
            fn (TaxData $tax) => "{$tax->id} - {$tax->name} ({$tax->rate}%)",
        );
    }

    protected function createExemptTax(Command $command): ?TaxData
    {
        $reasons = collect(TaxExemptionReason::cases())
            ->filter(fn (TaxExemptionReason $reason) => (int) substr($reason->value, 1) <= 16)
            ->map(fn (TaxExemptionReason $reason) => $reason->value)
            ->values()
            ->all();

        $reason = (string) $command->choice('Exemption reason for the new exempt tax', $reasons, TaxExemptionReason::M07->value);

        try {
            return Tax::create(TaxData::make([
                'name' => 'Isento',
                'rate' => 0,
                'type' => ItemTax::EXEMPT,
                'fiscalZone' => $this->fiscalZone,
                'exemptionReason' => TaxExemptionReason::from($reason),
            ]))->execute()->getTax();
        } catch (RequestFailedException $exception) {
            return $this->missing($command, "Moloni rejected the exempt tax ({$exception->getMessage()}). Create a 0% exempt tax in Moloni.");
        }
    }

    /**
     * @param  Collection<int, TaxData>  $taxes
     * @return array<string, string>
     */
    protected function otherTax(Command $command, Collection $taxes): array
    {
        $tax = $this->pickTax($command, $taxes, ItemTax::OTHER);

        if (is_null($tax)) {
            return [];
        }

        return [
            'MOLONI_TAX_OTHER_ID' => (string) $tax->id,
            'MOLONI_TAX_OTHER_RATE' => (string) $tax->rate,
        ];
    }
}
