# TODO

## Moloni client create

These required fields are sent as `0` until real values are defined:

- `maturity_date_id`
- `payment_method_id`
- `country_id`
- `language_id`

## Moloni invoice create

Sent as placeholders until real values are defined:

- `document_set_id` is `0`
- `product_id` is `0` and `name` is empty when the item has no id / name

`InvoiceData` inputs not sent:

- item `reference`, `type`, `amountDiscount` and `taxExemptionLaw`
- client fields other than `id` (Moloni only takes a `customer_id`)
- transport origin / destination `country` (needs the Moloni country id) and destination `dateTime`
- `output`: no PDF or ESC/POS is fetched (`documents/getPDFLink`)

Response:

- `sequence` is empty and `total`, `totalNet` and the ATCUD are not filled (needs `getOne`)
- errors returned with HTTP 200 are not detected

To confirm against the live API:

- every field is sent to every document endpoint (e.g. `payments` on FT, `expiration_date` on NC)
- `associated_documents.value` is sent as a gross amount
- GT is mapped to `billsOfLading`
