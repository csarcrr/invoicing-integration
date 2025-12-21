# Outputting Invoice Documents

Retrieve and store generated invoice documents (PDFs or ESC/POS data) after creating an invoice. By default, output is a PDF file.

## Save PDF to Storage

Save the invoice PDF to your local storage and get the file path:

```php
$invoice = Invoice::create();
// ...
$invoice = $invoice->execute();

$invoice->output()->save(); // saves to storage/private/output/ft_01p2025_1.pdf and outputs the path
```

The `save()` method stores the file in `storage_path` and returns the full file path for easy retrieval.

## Get ESC/POS Print Data

Generate ESC/POS data for thermal printers (when supported by the provider):

```php
$invoice = Invoice::create();
// ...
$invoice->asEscPos();
$invoice = $invoice->execute();

$invoice->output()->get();
```

Returns ESC/POS data as a string, or `null` if not supported. Check [provider support](/features?id=outputing ':target=_blank') for availability.

## Summary

- The default output is a PDF file.
- The `save()` method saves the output to the local disk in the `storage_path` and returns the file path.

- Use `output()` on the invoice to access the output handler.
- Use `save()` to store or retrieve the output data.
- The output can be a PDF file or ESC/POS data, depending on your needs.

See the tests for more usage examples and assertions.
