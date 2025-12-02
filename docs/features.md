# Features

This page documents the supported features and their implementation status for each provider.

### Legend

-   ✅ — Implemented
-   ❌ — Not Implemented
-   ⛔ — Not applicable

## Invoice Types

### FT (Fatura)

|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ✅        | ✅     |
| Set Item             | ✅        | ✅     |
| Set Payment          | ✅        | ✅     |
| Set Due Date         | ✅        | ✅     |
| Set Transport        | ✅        | ❌     |
| Set related Document | ✅        | ✅     |

### RG (Recibo)

|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ✅        | ✅     |
| Set Item             | ⛔        | ⛔     |
| Set Payment          | ✅        | ✅     |
| Set Due Date         | ⛔        | ⛔     |
| Set Transport        | ⛔        | ⛔     |
| Set related Document | ✅        | ✅     |

### FR

|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ✅        | ✅     |
| Set Item             | ✅        | ✅     |
| Set Payment          | ✅        | ✅     |
| Set Due Date         | ⛔        | ⛔     |
| Set Transport        | ⛔        | ⛔     |
| Set related Document | ✅        | ✅     |

### FS

|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ✅        | ✅     |
| Set Item             | ✅        | ✅     |
| Set Payment          | ✅        | ✅     |
| Set Due Date         | ⛔        | ⛔     |
| Set Transport        | ⛔        | ⛔     |
| Set related Document | ✅        | ✅     |

### NC

|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ✅        | ✅     |
| Set Item             | ❌        | ❌     |
| Set Payment          | ❌        | ❌     |
| Set Due Date         | ⛔        | ⛔     |
| Set Transport        | ⛔        | ⛔     |
| Set related Document | ❌        | ❌     |

## Client

|             | Invoicing | Vendus |
| ----------- | --------- | ------ |
| Name        | ✅        | ✅     |
| VAT         | ✅        | ✅     |
| Adress      | ✅        | ✅     |
| City        | ✅        | ✅     |
| Postal Code | ✅        | ✅     |
| Country     | ✅        | ✅     |
| E-mail      | ✅        | ✅     |
| Phone       | ✅        | ✅     |

## Item

|                        | Invoicing | Vendus |
| ---------------------- | --------- | ------ |
| Reference              | ✅        | ✅     |
| Item ID                | ❌        | ❌     |
| Description            | ✅        | ✅     |
| Price                  | ✅        | ✅     |
| Tax                    | ✅        | ✅     |
| Tax exempt with reason | ✅        | ✅     |
| Type                   | ✅        | ✅     |
| Percentage Discount    | ✅        | ✅     |
| Amount Discount        | ✅        | ✅     |

## Payment

|                              | Invoicing | Vendus |
| ---------------------------- | --------- | ------ |
| Amount                       | ✅        | ✅     |
| Method (with integration ID) | ✅        | ✅     |

## Transport

|                                                   | Invoicing | Vendus |
| ------------------------------------------------- | --------- | ------ |
| Origin (address, postal_code, city, country)      | ✅        | ✅     |
| Destination (address, postal_code, city, country) | ✅        | ✅     |
| Global Invoice Transport                          | ❌        | ❌     |
| Choose integration store for stock movement       | ❌        | ❌     |
| Set Vehicle License Plate                         | ✅        | ✅     |

---

For more details on each feature, see the relevant documentation sections or the source code.
