# Plan: `invoicing-integration:setup` command

A reusable artisan command that fetches every provider ID the config needs and writes them to `.env`. It works for every supported provider. Each lookup is a package operation (Contract, Data, provider class, Action with `match` on `Provider`, Facade). Operations a provider lacks throw `OperationNotSupportedException`.

Already in place:
- `Client::find()` searches by VAT on both providers, so it covers the Moloni no-VAT client (`999999990`).
- `PaymentMethod::find()` works on Moloni.

## ~~1. Company (Moloni only)~~ (done)

- Files:
  - `Contracts/IntegrationProvider/Company/ShouldFindCompany`
  - `Data/CompanyData`
  - `Provider/Moloni/Company/Find`
  - `Actions/CompanyAction::find()`
  - `Facades/Company`
- Vendus: not supported. A Vendus API key belongs to a single account.

| `CompanyData` | Moloni `companies/getAll` |
|---|---|
| `id` | `company_id` |
| `name` | `name` |
| `vat` | `vat` |

## 2. Tax (Moloni only)

- Files:
  - `ShouldFindTax` and `ShouldCreateTax`
  - `Data/TaxData`
  - `Provider/Moloni/Tax/Find` and `Provider/Moloni/Tax/Create`
  - `TaxAction::find()` and `TaxAction::create()`
  - `Facades/Tax`
- Vendus: not supported. Vendus takes a tax code, not an ID.

| `TaxData` | Moloni `taxes/getAll` / `taxes/insert` |
|---|---|
| `id` | `tax_id` |
| `name` | `name` |
| `rate` | `value` |
| `type` (`ItemTax`) | `vat_type`: NOR→NORMAL, INT→INTERMEDIATE, RED→REDUCED, ISE→EXEMPT, OUT→OTHER |
| `fiscalZone` | `fiscal_zone` |
| `exemptionReason` | `exemption_reason`, required by Moloni when the rate is 0 |
| (fixed on create) | `type=1` (percentage), `saft_type=1` (VAT), `stamp_tax=''`, `active_by_default=0` |

The command matches taxes by `vat_type`, not by rate.

## 3. Unit (both providers)

- Files:
  - `ShouldFindUnit`
  - `Data/UnitData`
  - `Provider/CegidVendus/Unit/Find` (`GET products/units`)
  - `Provider/Moloni/Unit/Find` (`measurementUnits/getAll`)
  - `UnitAction::find()`
  - `Facades/Unit`

| `UnitData` | Vendus | Moloni |
|---|---|---|
| `id` | `id` | `unit_id` |
| `name` | `title` | `name` |
| `shortName` | — | `short_name` |

## 4. PaymentMethod find on Vendus

- New class: `Provider/CegidVendus/PaymentMethod/Find` (`GET documents/paymentmethods`).
- `PaymentMethodAction::find()`: the Vendus case no longer throws.
- Type mapping: `NU`→MONEY, `CD`→MB, `CC`→CREDIT_CARD, `DNP`→CURRENT_ACCOUNT, `TB`→MONEY_TRANSFER, any other code → `null`.

## 5. Command `invoicing-integration:setup {--env-file=}`

- Files:
  - `src/Commands/SetupCommand.php`, registered with `hasCommand()`
  - `src/Commands/Setup/CegidVendus.php` and `Moloni.php`, implementing `ShouldSetupProvider`; each returns its env keys and values
- Steps:
  1. Select the provider.
  2. Ask for the credentials (secrets with `password()`).
  3. Set them in the runtime config and clear the cached `ProviderConfiguration`.
  4. Run the lookups.
  5. One match: pick it. Several: `select()`. None: create it or leave the key empty with a warning.
  6. Write the keys to `.env` (replace existing lines, append new ones). Default `base_path('.env')`; `--env-file` overrides it.
- Vendus: API key and mode, then PaymentMethod find and Unit find.
- Moloni:
  1. Ask for the 4 credentials.
  2. Select a company (Company find), set `company_id` and clear the cached config again.
  3. No-VAT client: `Client::find(vat 999999990)`.
  4. Taxes: Tax find. No ISE tax: ask for an exemption reason, then `Tax::create`.
  5. OTHER tax: optional prompt; its rate comes from the chosen tax.
  6. Units: Unit find.
- Delete `bin/sandbox-ids.sh`.

## Tests and docs

- Pest tests per Find and Create with the `providers` dataset and fixtures; unsupported operations assert `OperationNotSupportedException`.
- Command test with `Http::fake` and `expectsQuestion`.
- Docs: new `docs/companies/`, `docs/taxes/`, `docs/units/`; payment-methods pages for Vendus; `getting-started.md` for the command; `api-reference.md`; changelog for the Vendus changes.

## Open questions

1. Moloni has no Kg unit: also create one with `measurementUnits/insert` (adds a Unit Create action), or only warn?
2. No no-VAT client: create one with `Client::create` ("Consumidor Final"), or only warn?
3. Exempt tax reason: prompt with the M01–M16 choices from `TaxExemptionReason`, or always `M07`?
4. Public command with docs and changelog, or dev-only tool without docs?
