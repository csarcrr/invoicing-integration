# Features

This page documents the supported features and their implementation status for each provider.

### Legend
- ✅ — Implemented
- ❌ — Not Implemented
- ⛔ — Not applicable

## Invoice Types

### FT (Fatura)
|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ✅        | ✅     |
| Set Item             | ✅        | ✅     |
| Set Payment          | ✅        | ✅     |
| Set Transport        | ✅        | ❌     |
| Set related Document | ✅        | ✅     |

### RG (Recibo)
|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ✅        | ✅     |
| Set Item             | ⛔        | ⛔     |
| Set Payment          | ✅        | ✅     |
| Set Transport        | ⛔        | ⛔     |
| Set related Document | ✅        | ✅     |

### FR
|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ❌        | ❌     |
| Set Item             | ❌        | ❌     |
| Set Payment          | ❌        | ❌     |
| Set Transport        | ⛔        | ⛔     |
| Set related Document | ❌        | ❌     |

### FS
|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ❌        | ❌     |
| Set Item             | ❌        | ❌     |
| Set Payment          | ❌        | ❌     |
| Set Transport        | ⛔        | ⛔     |
| Set related Document | ❌        | ❌     |

### NC
|                      | Invoicing | Vendus |
| -------------------- | --------- | ------ |
| Set Client           | ❌        | ❌     |
| Set Item             | ❌        | ❌     |
| Set Payment          | ❌        | ❌     |
| Set Transport        | ⛔        | ⛔     |
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

---

For more details on each feature, see the relevant documentation sections or the source code.
