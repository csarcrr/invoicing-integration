# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Moloni provider. Both Vendus and Moloni are supported; see the Moloni configuration page in the docs.
- `DueDate::find()` on Vendus. Vendus has no due date ID, so `id` is `null` and the provider key (`now`, `30`, ...) is kept in additional data as `key`; `now` maps to `0` days.
- `Unit::create()` to create a measurement unit on Moloni and Vendus. `shortName` is sent to Moloni only.
- `Category::update()` to rename or move a stored category on Moloni and Vendus. `parentId` is sent to Moloni only.
- `Warehouse::create()` on Vendus, which creates a store.
- `DocumentType::find()` and `DocumentTypeData` to list the document types of Moloni and Vendus.
- `TaxExemption::find()` and `TaxExemptionData` to list the tax exemption reasons stored in the provider.
- `Warehouse::create()` on Moloni.
- `Warehouse::find()` and `WarehouseData` to list Moloni warehouses and Vendus stores.
- `Category::create()` to create a product category on Moloni and Vendus. `parentId` is sent to Moloni only.
- `Item::delete()` to remove a stored item on Moloni and Vendus.
- `Client::delete()` to remove a stored client on Moloni and Vendus.
- `Client::update()` to edit a stored client on Moloni and Vendus.
- `Invoice::find()` to list stored documents by type and client on Moloni and Vendus.
- `Invoice::cancel()` to cancel a stored document on Moloni and Vendus.
- `Invoice::pdf()` to get a stored document's PDF on Moloni and Vendus. `OutputData` gains `url`.
- `Invoice::fetch()` to read a stored document by its provider ID on Moloni and Vendus.
- `Category::find()` and `Category::fetch()` on Moloni and Vendus.
- `invoicing-integration:setup` command, which writes the provider IDs (payment methods, units, taxes, ...) to `.env`.
- `PaymentMethod::find()` on Vendus.
- `Unit::find()` to list the provider's units on Moloni and Vendus.
- `Tax::find()` and `Tax::create()` on Moloni.
- `Company::find()` on Moloni, to list the companies the user has access to.
- `ClientData` fields `paymentMethodId`, `paymentDay` and `salesmanId`, sent to Moloni only.

### Changed

- **Breaking:** the result getters are renamed to `get()`: `getInvoice()`, `getClient()`, `getItem()`, `getCategory()`, `getUnit()`, `getWarehouse()`, `getMovement()`, `getOutput()`, `getTax()`, `getPaymentMethod()`, `getDueDate()`, `getLanguage()` and `getCountry()`. `getList()` on find operations is also `get()`.
- **Breaking:** the `get` operation is renamed to `fetch`: `Client::get()`, `Item::get()`, `Invoice::get()` and `Category::get()` are now `Client::fetch()`, `Item::fetch()`, `Invoice::fetch()` and `Category::fetch()`. Their contracts are `ShouldFetchClient`, `ShouldFetchItem`, `ShouldFetchInvoice` and `ShouldFetchCategory`.
- `InvoiceData::$type` is now `?InvoiceType` and no longer defaults to `FT`. Without a type, Vendus requests do not send `type`, Moloni `Invoice::create()` posts to `documents/insert` and Moloni `Invoice::find()` lists from `documents/getAll`. An unknown document type in a provider response is `null` instead of `FT`.
- `InvoiceData::$dueDate` is sent on any document type. Setting it on a type other than FT no longer throws; the provider decides whether to accept it.
- Vendus requests now use API v1.2 (`https://www.vendus.pt/ws/v1.2/`). Item fields in `getAdditionalData()` use the v1.2 nested names: `prices.gross`, `tax.id`, `tax.exemption`, `tax.exemption_law`, `stock.control`, `stock.stores`.
- Vendus no longer sets `ClientData->date` from responses, because v1.2 does not return it. The `date` filter of `Client::find()` still works.
- `ItemData->taxExemptionReason` and `TaxData->exemptionReason` are now `?string` and take the provider's exemption code (e.g. `'M04'`).
- **Breaking:** `ClientData->externalReference` is renamed to `reference`.
- **Breaking:** Vendus sends `VENDUS_MODE` as `mode` when it is set. The valid values are `normal` and `tests`; `production` is rejected.
- **Breaking:** Vendus receipts (RG) take the invoice number (its `sequence`, e.g. `FT 01P2025/1`) as `relatedDocument`, not its `id`.
- **Breaking:** `AddressData::country` no longer defaults to `PT`. Set `country` on transport addresses to keep sending it.
- **Breaking:** Vendus `Client::create()` sends only the fields you set. A client created without `irsRetention` or `emailNotification` now has both switched off.
- **Breaking:** Vendus `Item::find()` also lists disabled items (`enabled` is `false`) and returns an empty list when nothing matches. A `category` filter is now sent as `category_id`.
- **Breaking:** Vendus `Client::find()` returns an empty list when nothing matches, instead of throwing `RequestFailedException`.
- **Breaking:** `Item::create()` and `Item::update()` return the stored item as the provider returns it, not the `ItemData` you passed.
- **Breaking:** read items map `unit` from the provider's `unit_id` against the configured units, and `enabled` from the provider instead of a fixed `true`. The top-level `quantity` of a read item is `null`.
- **Breaking:** `ItemData` built without `quantity`, `type`, `controlStock` or `enabled` reads `null` in those properties. `controlStock` and `enabled` are now `?bool`.
- **Breaking:** the Cegid Vendus provider is renamed to Vendus. `Provider::CEGID_VENDUS` is now `Provider::VENDUS` with value `Vendus`, the config key `CegidVendus` is now `Vendus`, the `CEGID_VENDUS_*` env variables are now `VENDUS_*`, the `Provider\CegidVendus` and `Exceptions\Providers\CegidVendus` namespaces are now `...\Vendus`, and `SupportedCegidVendusProperties` is now `SupportedVendusProperties`.

### Removed

- **Breaking:** the `TaxExemptionReason` enum and its `laws()` helper. Pass the exemption code as a string and get the codes from `TaxExemption::find()`. This affects Vendus as well as Moloni.

### Fixed

- Vendus and Moloni return the invoice ATCUD as `atcud`. An empty ATCUD is `null`.
- Vendus invoices with a client were rejected: the client now sends only the fields Vendus accepts, and `emailNotification` is sent as `send_email`.
- Vendus invoice items now send `id` and `name` (as `title`).
- Vendus invoices with an item `note`, item `percentageDiscount` or a `dueDate` were rejected: they are now sent as `text`, `discount_percentage` and `date_due`.
- Vendus `Client::create()` stored `Array` in unset text fields.

[Unreleased]: https://github.com/csarcrr/invoicing-integration/compare/v0.6.2...HEAD
