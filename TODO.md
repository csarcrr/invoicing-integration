# TODO

## Quality of Life

- Methods like `getX` should not exist. `get` methods should be agnostic and an abstraction method in the classes that use them, in order for the class to implement them.
    - Example: `getInvoice` -> `get`
    ```php
    <?php

    abstract class Base
    {
        abstract public function get(): mixed;
    }

    class Child extends Base
    {
        public function get(): InvoiceDto
        {
            return InvoiceDto::Active;
        }
    }
    ```
- Needs investigation: the output of an invoice might not always be PDF, it can be other types like ESCPOS - Vendus already supports this and needs to be verified in the Moloni situation how this can be achieved.
- Redeclaration of methods like in `ShouldFindClient.php` need to be re-thinked. Always re-typing the same method is not ok
    ```php
    // ShouldFindClient.php
    interface ShouldFindClient extends ShouldExecute, ShouldHavePagination, ShouldHavePayload
    {
        public function execute(): self;

        /**
        * @return Collection<int, mixed>
        */
        public function getList(): Collection;

        public function getClient(): ClientData;
    }

    // ShouldExecute.php
    interface ShouldExecute
    {
        public function execute(): self;
    }

    ```
- The src/Provider/Moloni/Client/Create.php@`ensureRequiredData` methods needs to go. This goes against the rules of the package: do not validate fields locally - let the provider handle that
- In tests/Feature/Client/UpdateTest.php on the test `returns the updated client`, that is not how we usually reference the files, nor the providers we are testing on the `with` method
- Unit tests that fake the lookups a provider class sends while building its payload are a temporary exception. Replace them with a different approach.
    - Moloni due dates (`maturityDates/getAll`): `tests/Unit/Client/Create/ProviderData/{PayloadTest,DefaultPayDueTest,CommercialTermsTest,CountryTest,LanguageTest,NumberTest}.php`, `tests/Unit/Client/Update/ProviderData/PayloadTest.php`, `tests/Unit/Invoice/Create/ProviderData/DueDateTest.php`
    - Payment methods: `tests/Unit/Invoice/Create/ProviderData/{PaymentTest,DueDateTest,TypeTest,RelatedDocumentTest}.php`, `tests/Unit/Invoice/Create/ProviderData/CreditNote/CreditNoteReasonTest.php`
    - Client by VAT: `tests/Unit/Invoice/Create/ProviderData/{ClientTest,TransportTest}.php`
    - Stored Moloni product (`products/getOne`): `tests/Unit/Item/Update/ProviderData/{PayloadTest,TaxTest}.php`
    - Moloni country, language, document set and OAuth grant: every Moloni unit test, through `mockConfiguration(Provider::MOLONI)` in `tests/Pest.php`
    - Tests that assert the lookup itself:
        - `tests/Unit/Invoice/Create/ProviderData/PaymentTest.php`: "fetches the provider payment methods once per invoice", "only sends payments on document types that accept them"
        - `tests/Unit/Invoice/Create/ProviderData/DueDateTest.php`: "fetches the provider due dates in a single page", "does not fetch the provider due dates when no due date is set"
        - `tests/Unit/Item/Update/ProviderData/PayloadTest.php`: "fails when no id is set, without any request", "fails when the unit is not mapped, without any request"
        - `tests/Unit/Item/Update/ProviderData/TaxTest.php`: "fails when the moloni tax is not configured, without any request"
- .claude/rules/provider-operations.md contains a rule:
    - - When a provider requires a value no DTO field carries, build it in the provider class (e.g. a random token) and keep it in the returned DTO's additional data. Never add a DTO field for one provider (see `.claude/rules/dtos.md`).
    - This is incorrect. We cannot always default to a random string. We need to find a way to support these fields without breaking the rule of not inventing fields
- on Vendus, creating a wharehouse, in nowhere it is getting specified that the type is a wharehouse
    - https://www.vendus.pt/ws/v1.2/#tag/Stores/paths/~1stores/post
    - this is a problem because, stores in vendus, are registers and these registers are tied to the subscription
        - this means that creating a register will create a new item for the subscription which will not work
        - and this is a problem because the API is not stating the type of register anywhere - investigate if it accepts `type`
