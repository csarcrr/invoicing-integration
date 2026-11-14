# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `DueDate::find()` on Cegid Vendus. Cegid Vendus has no due date ID, so `id` is `null` and the provider key (`now`, `30`, ...) is kept in additional data as `key`; `now` maps to `0` days.
- `Unit::create()` to create a measurement unit on Moloni and Cegid Vendus. `shortName` is sent to Moloni only.
- `Category::update()` to rename or move a stored category on Moloni and Cegid Vendus. `parentId` is sent to Moloni only.
- `Warehouse::create()` on Cegid Vendus, which creates a store.
- `DocumentType::find()` and `DocumentTypeData` to list the document types of Moloni and Cegid Vendus.
- `TaxExemption::find()` and `TaxExemptionData` to list the tax exemption reasons stored in the provider.
- `Warehouse::create()` on Moloni.
- `Warehouse::find()` and `WarehouseData` to list Moloni warehouses and Cegid Vendus stores.
- `Category::create()` to create a product category on Moloni and Cegid Vendus. `parentId` is sent to Moloni only.
- `Item::delete()` to remove a stored item on Moloni and Cegid Vendus.
- `Client::delete()` to remove a stored client on Moloni and Cegid Vendus.
- `Client::update()` to edit a stored client on Moloni and Cegid Vendus.
- `Invoice::find()` to list stored documents by type and client on Moloni and Cegid Vendus.
- `Invoice::cancel()` to cancel a stored document on Moloni and Cegid Vendus.
- `Invoice::pdf()` to get a stored document's PDF on Moloni and Cegid Vendus. `OutputData` gains `url`.
- `Invoice::get()` to read a stored document by its provider ID on Moloni and Cegid Vendus.

### Changed

- Cegid Vendus requests now use API v1.2 (`https://www.vendus.pt/ws/v1.2/`). Item fields in `getAdditionalData()` use the v1.2 nested names: `prices.gross`, `tax.id`, `tax.exemption`, `tax.exemption_law`, `stock.control`, `stock.stores`.
- Cegid Vendus no longer sets `ClientData->date` from responses, because v1.2 does not return it. The `date` filter of `Client::find()` still works.
- `ItemData->taxExemptionReason` and `TaxData->exemptionReason` are now `?string` and take the provider's exemption code (e.g. `'M04'`).

### Removed

- **Breaking:** the `TaxExemptionReason` enum and its `laws()` helper. Pass the exemption code as a string and get the codes from `TaxExemption::find()`. This affects Cegid Vendus as well as Moloni.

[Unreleased]: https://github.com/csarcrr/invoicing-integration/compare/v0.6.2...HEAD
