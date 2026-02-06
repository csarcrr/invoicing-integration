<?php

namespace CsarCrr\InvoicingIntegration\Provider;

abstract class Base
{
    abstract protected function fillAdditionalProperties(array $data): void;
}
