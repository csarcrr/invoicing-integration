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

`InvoiceData` inputs not sent:

- transport origin / destination `country` (needs the Moloni country id) and destination `dateTime`
- `output`: no PDF or ESC/POS is fetched (`documents/getPDFLink`)

Response:

- the ATCUD is not filled (`getOne` does not return it)
- errors returned with HTTP 200 are not detected

To confirm against the live API:

- GT and NC still send every field, whether or not their endpoint has it (e.g. `payments`, `expiration_date`)
- `associated_documents.value` is sent as a gross amount
- GT is mapped to `billsOfLading`
- `sequence` is built as `{saft_code} {document set name}/{number}` from `documents/getOne`
- `total` is `net_value` and `totalNet` is `net_value - taxes_value` from `documents/getOne`
