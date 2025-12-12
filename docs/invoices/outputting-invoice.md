# Outputting Invoice Documents


The output feature allows you to retrieve and store the generated invoice documents, such as PDFs or ESC/POS print data, after an invoice is created. This is useful for saving, displaying, or printing the invoice output in your application.

**By default, the output is a PDF file.**

The `output()` method provides access to the output handler for the invoice. If you do not specify a different format (such as by calling `asEscPos()`), the output will be a PDF document.

The `save()` method saves the output to the local disk, specifically in the `storage_path`, and returns the full file path to the saved file. This makes it easy to store and retrieve invoice documents from your application.

The examples below show how to use the output feature with the current API and variable names.

## Saving PDF Output to Storage


After creating an invoice, you can access its output and save it to your storage disk. For example:


```php
$integration = Invoice::create();
// ...
$invoice = $integration->invoice();

$invoice->output()->save(); // saves to storage/ft_01p2025_1.pdf and outputs the path
```


- `output()` returns an output handler for the invoice (e.g., PDF or ESC/POS data).
- `save()` saves the output file to the local disk in the `storage_path` and returns the full file path.
- You can use Laravel's `Storage` facade to check or manipulate the saved file.

## Outputting ESC/POS Data


You can also generate ESC/POS output for printing receipts:


```php
$integration = Invoice::create();
// ...
$invoice = $integration->asEscPos()->invoice();

$invoice->output()->get(); // outputs esc pos payload
```


- Calling `asEscPos()` on the integration instance configures the output for ESC/POS format.
- The `output()->save()` method will return the ESC/POS data as a string.

## Summary

- The default output is a PDF file.
- The `save()` method saves the output to the local disk in the `storage_path` and returns the file path.

- Use `output()` on the invoice to access the output handler.
- Use `save()` to store or retrieve the output data.
- The output can be a PDF file or ESC/POS data, depending on your needs.

See the tests for more usage examples and assertions.
