# Invoicing Integration

A provider-agnostic vocabulary for issuing Portuguese fiscal documents and managing the records they depend on (clients, items, stock, payment terms) across invoicing providers.

## Providers

**Provider**:
An external invoicing platform certified by the Portuguese tax authority that issues and stores fiscal documents on the user's behalf (Cegid Vendus, Moloni).
_Avoid_: Integration, platform, vendor

**Operation**:
One action a provider can perform on one kind of record, such as creating an invoice or moving stock. Not every provider supports every operation.
_Avoid_: Endpoint, feature

**Company**:
The business account in a provider that issues invoices. Cegid Vendus calls it the account.
_Avoid_: Account, tenant

## Fiscal documents

**Invoice**:
Any fiscal document issued through a provider, whatever its document type.
_Avoid_: Document, bill

**Document Type**:
The Portuguese fiscal category of an invoice: FT (Fatura), FR (Fatura-Recibo), FS (Fatura Simplificada), RG (Recibo), GT (Guia de Transporte) or NC (Nota de Crédito).
_Avoid_: Invoice type, kind

**Receipt**:
A document (RG) that records payment of one or more previously issued invoices.
_Avoid_: Payment document

**Credit Note**:
A document (NC) that corrects or cancels all or part of a previously issued invoice.
_Avoid_: Refund, return

**Credit Note Reason**:
The free-text justification a credit note must carry.

**Transport Guide**:
A document (GT) that accompanies goods in transit from an origin to a destination.
_Avoid_: Bill of lading, delivery note

**Related Document**:
A previously issued invoice that a receipt or credit note refers to.
_Avoid_: Parent document, linked invoice

**Sequence**:
The human-readable identifier of an issued invoice, combining its document type, series and number.
_Avoid_: Invoice number, reference

**Document Series**:
The numbering sequence an invoice is issued under. Moloni calls it a document set.
_Avoid_: Document set, series

**Cancellation**:
Setting an issued invoice to the cancelled state; the invoice is kept, not removed.
_Avoid_: Void, deletion

**ATCUD**:
The unique document code required by the Portuguese tax authority on every fiscal document.

**Output**:
The rendered form of an issued invoice, either a PDF or an ESC/POS receipt-printer stream.
_Avoid_: Print, file, attachment

**Total**:
The gross amount of an invoice, taxes included.

**Total Net**:
The amount of an invoice before taxes.

## Clients

**Client**:
The person or organization an invoice is issued to.
_Avoid_: Customer, buyer, account

**Reference**:
A code the developer chooses to identify a client in their own system. Moloni calls it the client number; Cegid Vendus calls it the external reference.
_Avoid_: External reference, client number

**VAT Number**:
A client's Portuguese tax identification number (NIF).
_Avoid_: Fiscal ID, tax ID, NIF

**IRS Retention**:
Withholding of Portuguese income tax applied to invoices issued to a client.
_Avoid_: Withholding

## Items

**Item**:
A product, service or other chargeable thing that can appear as a line on an invoice.
_Avoid_: Product, article, line

**Item Type**:
The fiscal nature of an item: product, service, other, tax or special tax.

**Item Tax**:
The VAT rate category applied to an item: normal, intermediate, reduced, exempt or other.
_Avoid_: VAT rate, tax rate

**Tax Exemption Reason**:
The official code (M01 to M99) that justifies applying the exempt item tax.
_Avoid_: Exemption code

**Tax Exemption Law**:
The specific legal article that backs a tax exemption reason.

**Category**:
A grouping of items within a provider account.
_Avoid_: Family, group

**Unit**:
The measure an item's quantity is expressed in, such as units or kilograms.

## Stock

**Warehouse**:
A location within a provider account where an item's stock is held. Cegid Vendus calls it a store: a record tied to the account's subscription whose type is either store or warehouse, so a Cegid Vendus warehouse is a store of type warehouse. Not a Cegid Vendus register, which is a point-of-sale till.
_Avoid_: Store, location, register

**Item Stock**:
The quantity of an item held in one warehouse.
_Avoid_: Inventory, stock level

**Stock Movement**:
A manual change to an item's stock: a positive quantity adds units, a negative quantity removes them.
_Avoid_: Stock adjustment, transfer

**Stock Control**:
Whether a provider tracks stock for an item at all.

## Payments

**Payment**:
An amount paid towards an invoice using one payment method.

**Payment Method**:
The means by which a payment is made: cash, Multibanco, credit card, bank transfer or current account.
_Avoid_: Payment type, tender

**Due Date**:
A reusable, account-level payment term stored in the provider, made of a name and a number of days. Moloni calls it a maturity date.
_Avoid_: Maturity date, payment term

**Due Date Term**:
The number of days, from a fixed list (0, 1, 7, 15, 30, 45, 60 or 90), a single invoice or a client's invoices are due in after issue. A term of 0 days means the invoice is due on the day of issue.
_Avoid_: Due date (when meaning one invoice)

## Provider names

The name each provider uses for a package term, where it differs or is worth stating.

| Package term | Moloni | Cegid Vendus |
| --- | --- | --- |
| Company | company | account |
| Client | customer | client |
| Reference | number | external reference |
| Item | product | product |
| Category | product category | product category |
| Unit | measurement unit | unit |
| Warehouse | warehouse | store (type store or warehouse) |
| Stock Movement | product stock | product stock |
| Invoice | document | document |
| Document Series | document set | none |
| Due Date | maturity date | due date |
| Tax Exemption Reason | tax exemption | tax exemption |
