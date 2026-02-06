# Features

Need to check if a feature is supported before you start coding? This page provides a quick reference matrix showing what's implemented across different providers and document types.

The columns show what's implemented for each provider: **Cegid Vendus**, **Moloni**, and **Invoice Express**.

**Document types at a glance:**

| Code | Name                                     | Use Case                                        |
| ---- | ---------------------------------------- | ----------------------------------------------- |
| FT   | Fatura (Invoice)                         | Standard invoice for sales on credit            |
| FR   | Fatura-Recibo (Invoice-Receipt)          | Invoice + immediate payment confirmation        |
| RG   | Recibo (Receipt)                         | Payment receipt for a previously issued invoice |
| FS   | Fatura Simplificada (Simplified Invoice) | Quick invoices for small amounts (< €1000)      |
| NC   | Nota de Crédito (Credit Note)            | Refunds, returns, or invoice corrections        |

### Legend

- ✅ — Implemented
- ❌ — Not Implemented
- ⛔ — Not applicable

# Client Management

Features for managing clients independently of invoices.

|               | Cegid Vendus | Moloni | Invoice Express |
| ------------- | ------------ | ------ | --------------- |
| Create Client | ✅           | ❌     | ❌              |
| Get Client    | ✅           | ❌     | ❌              |
| Update Client | ❌           | ❌     | ❌              |
| Delete Client | ❌           | ❌     | ❌              |
| List Clients  | ✅           | ❌     | ❌              |

# Invoicing

Features that apply when issuing an Invoicing.

<div class="matrix-wrapper">

<table class="matrix">
  <thead>
    <tr>
      <th rowspan="2">Feature</th>
      <th colspan="3">FT</th>
      <th colspan="3">FR</th>
      <th colspan="3">RG</th>
      <th colspan="3">FS</th>
      <th colspan="3">NC</th>
    </tr>
    <tr>
      <th>Cegid Vendus</th><th>Moloni</th><th>Invoice Express</th>
      <th>Cegid Vendus</th><th>Moloni</th><th>Invoice Express</th>
      <th>Cegid Vendus</th><th>Moloni</th><th>Invoice Express</th>
      <th>Cegid Vendus</th><th>Moloni</th><th>Invoice Express</th>
      <th>Cegid Vendus</th><th>Moloni</th><th>Invoice Express</th>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td><a href="/#/features?id=client">Client</a></td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
    </tr>
    <tr>
      <td><a href="/#/features?id=item">Item</a></td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>⛔</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
    </tr>
    <tr>
      <td><a href="/#/features?id=payment">Payment</a></td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
    </tr>
    <tr>
      <td>Due Date</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>⛔</td><td>❌</td><td>❌</td>
      <td>⛔</td><td>❌</td><td>❌</td>
      <td>⛔</td><td>❌</td><td>❌</td>
      <td>⛔</td><td>❌</td><td>❌</td>
    </tr>
    <tr>
      <td><a href="/#/features?id=transport">Transport</a></td>
      <td>❌</td><td>❌</td><td>❌</td>
      <td>⛔</td><td>❌</td><td>❌</td>
      <td>⛔</td><td>❌</td><td>❌</td>
      <td>⛔</td><td>❌</td><td>❌</td>
      <td>⛔</td><td>❌</td><td>❌</td>
    </tr>
    <tr>
      <td>Related Document</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>✅</td><td>❌</td><td>❌</td>
      <td>❌</td><td>❌</td><td>❌</td>
    </tr>
  </tbody>
</table>

</div>

### Common to all invoicing types

Features that are "shared" between multiple Invoicing types.

#### Outputing

|               | Cegid Vendus | Moloni | Invoice Express |
| ------------- | ------------ | ------ | --------------- |
| Save as PDF   | ✅           | ❌     | ❌              |
| Obtain ESCPOS | ✅           | ❌     | ❌              |

#### Client

|               | Cegid Vendus | Moloni | Invoice Express |
| ------------- | ------------ | ------ | --------------- |
| Name          | ✅           | ❌     | ❌              |
| VAT           | ✅           | ❌     | ❌              |
| Address       | ✅           | ❌     | ❌              |
| City          | ✅           | ❌     | ❌              |
| Postal Code   | ✅           | ❌     | ❌              |
| Country       | ✅           | ❌     | ❌              |
| E-mail        | ✅           | ❌     | ❌              |
| Phone         | ✅           | ❌     | ❌              |
| IRS Retention | ✅           | ❌     | ❌              |

#### Item

|                                 | Cegid Vendus | Moloni | Invoice Express |
| ------------------------------- | ------------ | ------ | --------------- |
| Reference                       | ✅           | ❌     | ❌              |
| Item ID                         | ❌           | ❌     | ❌              |
| Description                     | ✅           | ❌     | ❌              |
| Price                           | ✅           | ❌     | ❌              |
| Tax                             | ✅           | ❌     | ❌              |
| Tax exempt with reason          | ✅           | ❌     | ❌              |
| Type                            | ✅           | ❌     | ❌              |
| Percentage Discount             | ✅           | ❌     | ❌              |
| Amount Discount                 | ✅           | ❌     | ❌              |
| Related Document (Credit Notes) | ✅           | ❌     | ❌              |

#### Payment

|                              | Cegid Vendus | Moloni | Invoice Express |
| ---------------------------- | ------------ | ------ | --------------- |
| Amount                       | ✅           | ❌     | ❌              |
| Method (with integration ID) | ✅           | ❌     | ❌              |

#### Transport

Only applies to certain types of documents - See "Transport" compatibility in each type.

|                                                   | Cegid Vendus | Moloni | Invoice Express |
| ------------------------------------------------- | ------------ | ------ | --------------- |
| Origin (address, postal_code, city, country)      | ✅           | ❌     | ❌              |
| Destination (address, postal_code, city, country) | ✅           | ❌     | ❌              |
| Global Invoicing Transport                        | ❌           | ❌     | ❌              |
| Choose integration store for stock movement       | ❌           | ❌     | ❌              |
| Vehicle License Plate                             | ✅           | ❌     | ❌              |

---

For more details on each feature, see the relevant documentation sections or the source code.
