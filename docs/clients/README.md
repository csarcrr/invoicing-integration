# Clients

Register clients in the invoicing provider to reuse their details across invoices and take advantage of provider features like automatic email notifications.

## When to Use Client Management

**Register clients when:**

- You have repeat customers
- You want the provider to track customer history
- You need features like automatic invoice emails

**Use inline client data when:**

- It's a one-time purchase
- You don't need to track the customer in the provider
- You prefer to manage customer data in your own system

## Available Operations

- [Creating a Client](creating-a-client.md) - Register new customers
- [Getting a Client](getting-a-client.md) - Retrieve existing customer information
- [Finding Clients](finding-clients.md) - Search and paginate your customer base

## Quick Example: Registering a Customer

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Facades\Client;

// Register the customer
$clientData = ClientData::make([
    'name' => 'TechStore Portugal Lda',
    'vat' => 'PT509876543',
    'email' => 'invoices@techstore.pt',
    'address' => 'Zona Industrial do Porto, Lote 15',
    'city' => 'Porto',
    'postalCode' => '4100-000',
    'country' => 'PT',
    'phone' => '220123456',
    'emailNotification' => true, // Auto-send invoices to their email
    'defaultPayDue' => 30,       // Payment due in 30 days
]);

$client = Client::create($clientData)->execute()->getClient();

// Store their provider ID for future orders
$customer->provider_client_id = $client->id;
$customer->save();
```

Sample response (`$client->toArray()`):

```json
{
    "id": 98765,
    "name": "TechStore Portugal Lda",
    "vat": "PT509876543",
    "email": "invoices@techstore.pt",
    "address": "Zona Industrial do Porto, Lote 15",
    "city": "Porto",
    "postalCode": "4100-000",
    "country": "PT",
    "defaultPayDue": 30,
    "emailNotification": true
}
```

## Using Registered Clients in Orders

Once registered, use the client for all their orders:

```php
use CsarCrr\InvoicingIntegration\Data\ClientData;
use CsarCrr\InvoicingIntegration\Data\ItemData;
use CsarCrr\InvoicingIntegration\Facades\Client;
use CsarCrr\InvoicingIntegration\Facades\Invoice;

// Retrieve the stored client
$clientData = ClientData::make(['id' => $customer->provider_client_id]);
$client = Client::get($clientData)->execute()->getClient();

// Create the invoice
$invoice = Invoice::create();
$invoice->client($client);

$item = ItemData::make([
    'reference' => 'LAPTOP-PRO',
    'note' => 'Business Laptops (50 units)',
    'price' => 65000, // €650.00
    'quantity' => 50,
]);
$invoice->item($item);

// Payment due in 30 days
$invoice->dueDate(Carbon::now()->addDays(30));

$result = $invoice->execute()->getInvoice();
```

## Inline vs. Registered Clients

You don't have to register every customer. Here's when to use each approach:

```php
// Option 1: Inline client (for one-time purchases)
$invoice = Invoice::create();
$invoice->client(ClientData::make([
    'name' => 'Individual Buyer',
    'vat' => 'PT123456789',
]));
// Client details are used once and not stored in the provider

// Option 2: Registered client (for repeat customers)
$client = Client::get(ClientData::make(['id' => $storedId]))->execute()->getClient();
$invoice = Invoice::create();
$invoice->client($client);
// Provider tracks all invoices for this client
```

## Accessing Client Properties

All DTOs expose **typed public properties**:

```php
$client = Client::get(ClientData::make(['id' => 12345]))->execute()->getClient();

// Access values directly
$client->name;             // "TechStore Portugal Lda"
$client->vat;              // "PT509876543"
$client->email;            // "invoices@techstore.pt"
$client->defaultPayDue;    // 30
$client->emailNotification; // true
```

### Provider-Specific Properties

Some providers return additional fields not explicitly mapped. Access them via `toArray()`:

```php
$payload = $client->toArray();

$status = $payload['status'] ?? null;
$priceGroup = $payload['price_group']['name'] ?? null;
$balance = $payload['balance'] ?? null;
```

## Common Scenarios

### Multiple Locations

```php
// Main office
$headquarters = Client::create(ClientData::make([
    'name' => 'Empresa ABC - Sede',
    'vat' => 'PT501234567',
    'email' => 'finance@empresaabc.pt',
    'address' => 'Av. da Liberdade, 100',
    'city' => 'Lisboa',
    'notes' => 'Main corporate account - HQ',
]))->execute()->getClient();

// Porto branch (same VAT, different billing address)
$portoBranch = Client::create(ClientData::make([
    'name' => 'Empresa ABC - Porto',
    'vat' => 'PT501234567',
    'email' => 'porto@empresaabc.pt',
    'address' => 'Rua de Cedofeita, 200',
    'city' => 'Porto',
    'notes' => 'Porto branch office',
]))->execute()->getClient();
```

### Custom Payment Terms

```php
$reseller = Client::create(ClientData::make([
    'name' => 'Distribuidor Norte Lda',
    'vat' => 'PT509999888',
    'email' => 'orders@distrinorte.pt',
    'defaultPayDue' => 60,  // 60 days payment terms
    'notes' => '60-day terms approved',
    'emailNotification' => true,
]))->execute()->getClient();
```

---

**Tips:**

- Store the provider-assigned `id` in your database for future reference
- Use `emailNotification: true` to have invoices sent automatically
- Use `defaultPayDue` to set payment terms
- Use the `notes` field for internal reference (not shown on invoices)

---

Continue to: [Creating a Client](creating-a-client.md)
