<?php

namespace CsarCrr\InvoicingIntegration\Contracts;

interface ShouldExecute
{
    public function execute () : self;
}
