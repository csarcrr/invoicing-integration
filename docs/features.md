# Features

This page documents the supported features and their implementation status for each provider.

### Legend

- ✅ — Implemented
- ❌ — Not Implemented
- ⛔ — Not applicable

# Client Management

Features for managing clients independently of invoices.

|               | Invoicing | Cegid Vendus |
| ------------- | --------- | ------------ |
| Create Client | ✅        | ✅           |
| Get Client    | ✅        | ✅           |
| Update Client | ❌        | ❌           |
| Delete Client | ❌        | ❌           |
| List Clients  | ✅        | ✅           |

# Invoicing

Features that apply when issuing an Invoicing.

<div class="matrix-wrapper">

<table class="matrix">
  <thead>
    <tr>
      <th rowspan="2">Feature</th>
      <th colspan="2">FT</th>
      <th colspan="2">FR</th>
      <th colspan="2">RG</th>
      <th colspan="2">FS</th>
      <th colspan="2">NC</th>
    </tr>
    <tr>
      <th>Invoicing</th><th>Cegid Vendus</th>
      <th>Invoicing</th><th>Cegid Vendus</th>
      <th>Invoicing</th><th>Cegid Vendus</th>
      <th>Invoicing</th><th>Cegid Vendus</th>
      <th>Invoicing</th><th>Cegid Vendus</th>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td><a href="/#/features?id=client">Client</a></td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
    </tr>
    <tr>
      <td><a href="/#/features?id=item">Item</a></td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
      <td>⛔</td><td>⛔</td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
    </tr>
    <tr>
      <td><a href="/#/features?id=payment">Payment</a></td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
    </tr>
    <tr>
      <td>Due Date</td>
      <td>✅</td><td>✅</td>
      <td>⛔</td><td>⛔</td>
      <td>⛔</td><td>⛔</td>
      <td>⛔</td><td>⛔</td>
      <td>⛔</td><td>⛔</td>
    </tr>
    <tr>
      <td><a href="/#/features?id=transport">Transport</a></td>
      <td>✅</td><td>❌</td>
      <td>⛔</td><td>⛔</td>
      <td>⛔</td><td>⛔</td>
      <td>⛔</td><td>⛔</td>
      <td>⛔</td><td>⛔</td>
    </tr>
    <tr>
      <td>Related Document</td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
      <td>✅</td><td>✅</td>
      <td>❌</td><td>❌</td>
    </tr>
  </tbody>
</table>

</div>

### Common to all invoicing types

Features that are "shared" between multiple Invoicing types.

#### Outputing

|               | Invoicing | Cegid Vendus |
| ------------- | --------- | ------------ |
| Save as PDF   | ✅        | ✅           |
| Obtain ESCPOS | ✅        | ✅           |

#### Client

|               | Invoicing | Cegid Vendus |
| ------------- | --------- | ------------ |
| Name          | ✅        | ✅           |
| VAT           | ✅        | ✅           |
| Address       | ✅        | ✅           |
| City          | ✅        | ✅           |
| Postal Code   | ✅        | ✅           |
| Country       | ✅        | ✅           |
| E-mail        | ✅        | ✅           |
| Phone         | ✅        | ✅           |
| IRS Retention | ✅        | ✅           |

#### Item

|                                 | Invoicing | Cegid Vendus |
| ------------------------------- | --------- | ------------ |
| Reference                       | ✅        | ✅           |
| Item ID                         | ❌        | ❌           |
| Description                     | ✅        | ✅           |
| Price                           | ✅        | ✅           |
| Tax                             | ✅        | ✅           |
| Tax exempt with reason          | ✅        | ✅           |
| Type                            | ✅        | ✅           |
| Percentage Discount             | ✅        | ✅           |
| Amount Discount                 | ✅        | ✅           |
| Related Document (Credit Notes) | ✅        | ✅           |

#### Payment

|                              | Invoicing | Cegid Vendus |
| ---------------------------- | --------- | ------------ |
| Amount                       | ✅        | ✅           |
| Method (with integration ID) | ✅        | ✅           |

#### Transport

Only applies to certain types of documents - See "Transport" compatibility in each type.

|                                                   | Invoicing | Cegid Vendus |
| ------------------------------------------------- | --------- | ------------ |
| Origin (address, postal_code, city, country)      | ✅        | ✅           |
| Destination (address, postal_code, city, country) | ✅        | ✅           |
| Global Invoicing Transport                        | ❌        | ❌           |
| Choose integration store for stock movement       | ❌        | ❌           |
| Vehicle License Plate                             | ✅        | ✅           |

---

For more details on each feature, see the relevant documentation sections or the source code.
