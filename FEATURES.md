# Features

### ✅ - Implemented / ❌ - Not Implemented / ⛔ - Not applicable

## Invoices Types

### FT

|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ✅        | ✅     |
| Set Item             | ✅        | ✅     |
| Set Payment          | ✅        | ✅     |
| Set Transport        | ✅        | ❌     |
| Set related Document | ✅        | ✅     |

### RG

|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ✅        | ✅     |
| Set Item             | ⛔        | ⛔     |
| Set Payment          | ✅        | ✅     |
| Set Transport        | ⛔        | ⛔     |
| Set related Document | ✅        | ✅     |

### FS

|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ❌        | ❌     |
| Set Item             | ❌        | ❌     |
| Set Payment          | ❌        | ❌     |
| Set Transport        | ❌        | ❌     |
| Set Transport        | ⛔        | ⛔     |
| Set related Document | ❌        | ❌     |

### NC

|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ❌        | ❌     |
| Set Item             | ❌        | ❌     |
| Set Payment          | ❌        | ❌     |
| Set Transport        | ❌        | ❌     |
| Set related Document | ❌        | ❌     |

## Client

|      | Invoicing | Vendus |
| ---- | --------- | ------ |
| Name | ✅        | ✅     |
| VAT  | ✅        | ✅     |

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
| Set Vehicle License Plate                         | ❌        | ❌     |
